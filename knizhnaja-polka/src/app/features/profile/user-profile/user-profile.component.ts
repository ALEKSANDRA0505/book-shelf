import { Component, HostListener, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, FormControl } from '@angular/forms';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { UserService } from '../../../core/services/user.service';
import { User } from '../../../core/models/user.model';
import { Achievement } from '../../../core/models/achievement.model';
import { WishlistItem } from '../../../core/models/wishlist-item.model';
import { Review } from '../../../core/models/review.model';
import { Subscription as UserSubscriptionModel } from '../../../core/models/subscription.model';
import { Book } from '../../../core/models/book.model';
import { firstValueFrom, Subscription as RxJsSubscription } from 'rxjs';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { ReviewService } from '../../../core/services/review.service';
import { catchError, finalize, of } from 'rxjs';
interface ReadBook {
  id_read_book?: number;
  title: string;
  author: string;
  added_at?: string;
  imageUrl?: string;
}
@Component({
  selector: 'app-user-profile',
  templateUrl: './user-profile.component.html',
  styleUrls: ['./user-profile.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule, ReactiveFormsModule, RouterModule]
})
export class UserProfileComponent implements OnInit, OnDestroy {
  searchTerm: string = '';
  isEditModalOpen: boolean = false;
  isSettingsModalOpen: boolean = false;
  currentTab: string = 'wishlist';
  
  editForm: FormGroup;
  settingsForm: FormGroup;
  isSaving: boolean = false;
  saveError: string | null = null;
  isUploadingAvatar: boolean = false;
  uploadError: string | null = null;
  wishlist: Book[] = [];
  userReviews: Review[] = [];
  subscriptions: UserSubscriptionModel[] = [];
  readBooks: ReadBook[] = [];
  isLoadingReadBooks: boolean = false;
  isLoadingWishlist: boolean = false;
  isLoadingReviews: boolean = false;
  isLoadingSubscriptions: boolean = false;
  readBooksError: string | null = null;
  wishlistError: string | null = null;
  reviewsError: string | null = null;
  subscriptionsError: string | null = null;
  newBook: { title: string, author: string } = { title: '', author: '' };
  private profileSubscription: RxJsSubscription | null = null;
  private currentUserId: number | null = null;
  tabs = [
    { id: 'wishlist', name: 'Виш-лист' },
    { id: 'reviews', name: 'Мои рецензии' },
    { id: 'subscriptions', name: 'Подписки' }
  ];
  constructor(
    private fb: FormBuilder,
    public authService: AuthService,
    private router: Router,
    private userService: UserService,
    private subscriptionService: SubscriptionService,
    private reviewService: ReviewService
  ) {
    this.editForm = this.fb.group({
      username: ['', Validators.required],
      age: [null],
      city: [''],
      status: [''],
      about_me: ['']
    });
    this.settingsForm = this.fb.group({
      readBooks: [0, Validators.min(0)],
      reading_goal: [1, Validators.min(1)]
    });
  }
  ngOnInit(): void {
    this.loadInitialData();
    this.profileSubscription = this.authService.currentUser$.subscribe(profile => {
      if (profile) {
        this.currentUserId = profile.id_user;
        this.loadSubscriptions();
        if (this.currentTab === 'reviews') {
          this.loadUserReviews();
        }
      } else {
        this.currentUserId = null;
        this.subscriptions = [];
        this.userReviews = [];
      }
    });
  }
  ngOnDestroy(): void {
    if (this.profileSubscription) {
      this.profileSubscription.unsubscribe();
    }
  }
  loadInitialData(): void {
    this.loadReadBooks();
    this.loadWishlist();
  }
  loadReadBooks(): void {
    this.isLoadingReadBooks = true;
    this.readBooksError = null;
    this.userService.getReadBooks().subscribe({
      next: (books) => {
        this.readBooks = books;
        this.isLoadingReadBooks = false;
        console.log('Read books loaded:', books);
      },
      error: (err: any) => {
        console.error('Error loading read books:', err);
        this.readBooksError = "Не удалось загрузить прочитанные книги.";
        this.isLoadingReadBooks = false;
      }
    });
  }
  loadWishlist(): void {
    console.log('Loading wishlist...');
    this.isLoadingWishlist = true;
    this.wishlistError = null;
    this.userService.getWishlist().subscribe({
      next: (books) => {
        this.wishlist = books;
        this.isLoadingWishlist = false;
        console.log('Wishlist loaded:', books);
      },
      error: (err: any) => {
        console.error('Error loading wishlist:', err);
        this.wishlistError = "Не удалось загрузить виш-лист.";
        this.isLoadingWishlist = false;
        if (err.status === 401) {
          this.wishlistError = "Ошибка авторизации при загрузке виш-листа.";
        }
      }
    });
  }
  loadUserReviews(): void {
    if (!this.currentUserId) {
      this.reviewsError = "Не удалось получить ID пользователя для загрузки рецензий.";
      return;
    }
    console.log('Загрузка рецензий пользователя...');
    this.isLoadingReviews = true;
    this.reviewsError = null;
    this.reviewService.getReviews({ user_id: this.currentUserId })
      .pipe(
        catchError(err => {
          console.error('Ошибка загрузки рецензий пользователя:', err);
          this.reviewsError = 'Не удалось загрузить ваши рецензии.';
          return of([]);
        }),
        finalize(() => this.isLoadingReviews = false)
      )
      .subscribe(reviews => {
        console.log('Рецензии пользователя загружены:', reviews);
        this.userReviews = reviews;
      });
  }
  /**
   * Генерирует массив CSS-классов для отображения звезд рейтинга.
   * @param rating Рейтинг (например, 3.5)
   * @returns Массив строк с классами (fa-star, fa-star-half-alt, far fa-star)
   */
  getStars(rating: number): string[] {
    const stars: string[] = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < fullStars; i++) {
      stars.push('fas fa-star');
    }
    if (hasHalfStar) {
      stars.push('fas fa-star-half-alt');
    }
    for (let i = 0; i < emptyStars; i++) {
      stars.push('far fa-star');
    }
    return stars;
  }
  /**
   * Вычисляет процент прочитанных книг относительно цели.
   * @param profile Текущий объект пользователя.
   * @returns Процент от 0 до 100.
   */
  readBooksPercent(profile: User | null): number {
    if (!profile || !profile.reading_goal || profile.reading_goal <= 0) {
      return 0;
    }
    const readCount = profile.read_books_count || 0;
    const percent = Math.min(100, Math.round((readCount / profile.reading_goal) * 100));
    return percent;
  }
  declOfNum(number: number | null | undefined, titles: string[]): string {
    number = number || 0;
    const cases = [2, 0, 1, 1, 1, 2];
    return titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
  }
  setCurrentTab(tabId: string): void {
    this.currentTab = tabId;
    if (tabId === 'reviews' && this.userReviews.length === 0) {
      this.loadUserReviews();
    }
    if (tabId === 'subscriptions' && this.subscriptions.length === 0) {
      this.loadSubscriptions();
    }
  }
  async openEditModal(): Promise<void> {
    const profile = await firstValueFrom(this.authService.currentUser$);
    if (!profile) return;
    this.editForm.patchValue({
      username: profile.username,
      age: profile.age,
      city: profile.city,
      status: profile.status,
      about_me: profile.about_me
    });
    this.isEditModalOpen = true;
  }
  closeEditModal(): void {
    this.isEditModalOpen = false;
  }
  async openSettingsModal(): Promise<void> {
    const profile = await firstValueFrom(this.authService.currentUser$);
    if (!profile) return;
    this.settingsForm.patchValue({
      readBooks: profile.read_books_count || 0,
      reading_goal: profile.reading_goal || 50
    });
    this.isSettingsModalOpen = true;
  }
  closeSettingsModal(): void {
    this.isSettingsModalOpen = false;
  }
  async saveEditForm(): Promise<void> {
    this.saveError = null;
    const currentProfile = await firstValueFrom(this.authService.currentUser$);
    if (!this.editForm.valid || !currentProfile) {
      this.saveError = 'Не удалось получить данные пользователя или форма невалидна.';
      return;
    }
    this.isSaving = true;
    const updatedData: Partial<User> = {
      id_user: currentProfile.id_user,
      username: this.editForm.value.username,
      age: this.editForm.value.age,
      city: this.editForm.value.city,
      status: this.editForm.value.status,
      about_me: this.editForm.value.about_me
    };
    this.userService.updateUserProfile(updatedData).subscribe({
      next: (response) => {
        this.isSaving = false;
        console.log('Профиль успешно обновлен:', response);
        this.authService.loadCurrentUser().subscribe();
        this.closeEditModal();
      },
      error: (err) => {
        this.isSaving = false;
        this.saveError = err.error?.error || 'Не удалось обновить профиль. Попробуйте позже.';
        console.error('Ошибка обновления профиля:', err);
      }
    });
  }
  async saveSettingsForm(): Promise<void> {
    const currentProfile = await firstValueFrom(this.authService.currentUser$);
    if (this.settingsForm.valid && currentProfile) {
      const updatedGoal = this.settingsForm.value.reading_goal;
      const readBooksFromForm = this.settingsForm.value.readBooks;
      
      console.log('Сохранение настроек (цель):', updatedGoal);
      console.log('Сохранение настроек (прочитано):', readBooksFromForm);
      console.log('Список прочитанных книг для сохранения (локальный):', this.readBooks);
      this.userService.updateUserSettings({
        id_user: currentProfile.id_user, 
        reading_goal: updatedGoal, 
        read_books_count: readBooksFromForm
      }).subscribe({
        next: () => {
          console.log('Настройки успешно сохранены');
          this.authService.loadCurrentUser().subscribe();
      this.closeSettingsModal();
        },
        error: (err: any) => {
          console.error('Ошибка сохранения настроек:', err);
        }
      });
    }
  }
  addBook(): void {
    if (this.newBook.title && this.newBook.author) {
      const bookToAdd = { ...this.newBook };
      this.userService.addReadBook(bookToAdd).subscribe({
        next: (addedBook) => {
          console.log('Book added successfully:', addedBook);
          this.readBooks.unshift(addedBook);
      this.newBook = { title: '', author: '' };
          this.authService.loadCurrentUser().subscribe();
        },
        error: (err: any) => {
          console.error('Error adding book:', err);
        }
      });
    } else {
    }
  }
  /**
   * Обработчик события выбора файла для аватара.
   * @param event Событие изменения input[type=file]
   */
  onFileSelected(event: Event): void {
    const element = event.currentTarget as HTMLInputElement;
    let fileList: FileList | null = element.files;
    this.uploadError = null;
    if (fileList && fileList.length > 0) {
      const file = fileList[0];
      console.log('Выбран файл:', file.name, file.type, file.size);
      const allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        this.uploadError = 'Пожалуйста, выберите файл в формате PNG, JPG или GIF.';
        element.value = '';
        return;
      }
      this.isUploadingAvatar = true;
      this.userService.updateProfilePicture(file).subscribe({
        next: (response) => {
          console.log('Аватар успешно загружен:', response);
          this.authService.loadCurrentUser().subscribe(() => {
            this.isUploadingAvatar = false;
          });
        },
        error: (err: any) => {
          console.error('Ошибка загрузки аватара:', err);
          this.uploadError = err.error?.message || 'Не удалось загрузить аватар. Попробуйте позже.';
          this.isUploadingAvatar = false;
        }
      });
    } else {
      console.log('Файл не выбран');
    }
    element.value = '';
  }
  @HostListener('window:click', ['$event'])
  onWindowClick(event: any): void {
    if (event.target.classList.contains('modal')) {
      this.closeEditModal();
      this.closeSettingsModal();
    }
  }
  logout(): void {
    this.authService.logout();
    this.router.navigate(['/auth/sign-in']);
    console.log('User logged out');
  }
  loadSubscriptions(): void {
    console.log('Loading subscriptions...');
    this.isLoadingSubscriptions = true;
    this.subscriptionsError = null;
    this.userService.getSubscriptions().subscribe({
        next: (subs) => {
            this.subscriptions = subs;
            this.isLoadingSubscriptions = false;
            console.log('Subscriptions loaded:', this.subscriptions);
        },
        error: (err) => {
            console.error('Error loading subscriptions:', err);
            this.subscriptionsError = 'Не удалось загрузить подписки.';
            this.isLoadingSubscriptions = false;
        }
    });
  }
  /**
   * Удаляет рецензию пользователя.
   * @param reviewId ID рецензии для удаления
   */
  deleteReview(reviewId: number): void {
    if (!reviewId) return;
    
    if (confirm('Вы действительно хотите удалить эту рецензию?')) {
      console.log('Удаление рецензии:', reviewId);
      this.reviewService.deleteReview(reviewId).subscribe({
        next: (response) => {
          console.log('Рецензия успешно удалена:', response);
          this.loadUserReviews();
        },
        error: (err) => {
          console.error('Ошибка удаления рецензии:', err);
          alert('Не удалось удалить рецензию. Попробуйте позже.');
        }
      });
    }
  }
}