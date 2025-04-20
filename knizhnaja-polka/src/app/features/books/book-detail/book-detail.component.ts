import { Component, OnInit, ViewChild, ElementRef, AfterViewInit, OnDestroy } from '@angular/core';
import { Location, CommonModule, DatePipe } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { ActivatedRoute } from '@angular/router';
import { Book } from '../../../core/models/book.model';
import { Review } from '../../../core/models/review.model';
import { BookService } from '../../../core/services/book.service';
import { AuthService } from '../../../core/services/auth.service';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { UserService } from '../../../core/services/user.service';
import { Subscription } from 'rxjs';
import { forkJoin, of } from 'rxjs';
import { catchError, finalize } from 'rxjs/operators';

interface SimilarBookDisplay {
  id: number;
  title: string;
  imageUrl: string | null | undefined; 
}
@Component({
  selector: 'app-book-detail',
  templateUrl: './book-detail.component.html',
  styleUrls: ['./book-detail.component.css'],
  standalone: true,
  imports: [CommonModule, RouterModule, DatePipe]
})
export class BookDetailComponent implements OnInit, AfterViewInit, OnDestroy {
  @ViewChild('similarBooksGrid') similarBooksGrid!: ElementRef;
  book: Book | null = null;
  currentReview: Review | null = null;
  similarBooks: SimilarBookDisplay[] = [];
  reviews: Review[] = [];
  currentReviewIndex = 0;
  isInWishlist = false;
  bookId: number | null = null;
  isLoading: boolean = true;
  error: string | null = null;
  isWishlistLoading = false;
  isLoadingReviews = false;
  reviewsError: string | null = null;
  private authSubscription: Subscription | null = null;
  isLoadingSimilarBooks = false;
  isAuthorSubscribed = false;
  isCurrentUserAuthor = false;
  private currentUserId: number | null = null;
  constructor(
    private route: ActivatedRoute,
    private location: Location,
    private router: Router,
    private bookService: BookService,
    private authService: AuthService,
    private subscriptionService: SubscriptionService,
    private userService: UserService
  ) {
    this.authSubscription = this.authService.currentUser$.subscribe(user => {
      this.currentUserId = user?.id_user || null;
    });
  }
  ngOnInit(): void {
    this.route.queryParamMap.subscribe(queryParams => {
      const id = queryParams.get('id');
      if (id) {
        this.bookId = +id;
        console.log('Book ID from query params:', this.bookId);
        this.loadBookDetails(this.bookId);
        this.loadReviews(this.bookId);
        
        this.authSubscription = this.authService.isLoggedIn$.subscribe(isAuth => {
          if (isAuth && this.bookId) {
            this.checkWishlistStatus(this.bookId);
          } else {
            this.isInWishlist = false;
          }
        });
      } else {
        console.error('Book ID not found in query parameters');
        this.error = 'ID книги не найден.';
        this.isLoading = false;
      }
    });
  }
  ngOnDestroy(): void {
    if (this.authSubscription) {
      this.authSubscription.unsubscribe();
    }
  }
  ngAfterViewInit(): void {
  }
  loadBookDetails(id: number): void {
    console.log('Загрузка деталей книги ID:', id);
    this.isLoading = true;
    this.error = null;
    this.bookService.getBookById(id).subscribe({
      next: (data) => {
        this.book = data;
        this.isLoading = false;
        console.log('Book details loaded:', this.book);
        if (this.book && this.book.genres && this.book.genres.length > 0) {
            this.loadSimilarBooks(this.book.genres[0].id_genre, this.book.id_book); 
        } else {
            this.similarBooks = [];
            this.isLoadingSimilarBooks = false;
        }
      },
      error: (err) => {
        console.error('Error loading book details:', err);
        this.error = `Не удалось загрузить детали книги (ID: ${id}). ${err.message || ''}`.trim();
        this.isLoading = false;
      }
    });
  }
  loadReviews(bookId: number): void {
    this.isLoadingReviews = true;
    this.reviewsError = null;
    
    this.bookService.getReviewsForBook(bookId).subscribe({
      next: (reviews: Review[]) => {
        this.reviews = reviews;
        console.log('Загружены рецензии:', reviews);
        
        if (reviews.length > 0) {
          this.currentReviewIndex = 0;
          this.currentReview = reviews[0];
          
          this.loadUserInfoForReviews();
          
          this.checkIfCurrentUserIsAuthor();
          
          if (this.authService.isLoggedIn() && this.currentReview.id_user) {
            this.checkSubscriptionStatus(this.currentReview.id_user);
          }
        }
        
        this.isLoadingReviews = false;
      },
      error: (err) => {
        console.error(`Ошибка при загрузке рецензий для книги ${bookId}:`, err);
        this.reviewsError = 'Не удалось загрузить рецензии.';
        this.isLoadingReviews = false;
      }
    });
  }
  
  /**
   * Загружает информацию о пользователях для всех рецензий
   */
  loadUserInfoForReviews(): void {
    if (!this.reviews || this.reviews.length === 0) return;
    
    this.reviews.forEach(review => {
      if (review.id_user) {
        this.userService.getAuthorById(review.id_user).pipe(
          catchError(err => {
            console.error(`Ошибка загрузки информации о пользователе ${review.id_user}:`, err);
            return of(null);
          })
        ).subscribe(user => {
          if (user) {
            review.user = user;
            console.log(`Загружена информация о пользователе для рецензии ${review.id_review}:`, user);
          }
        });
      }
    });
  }
  loadSimilarBooks(genreId: number, currentBookId: number): void {
    console.log(`Загрузка похожих книг для жанра ID: ${genreId}, исключая книгу ID: ${currentBookId}`);
    this.isLoadingSimilarBooks = true;
    this.similarBooks = [];
    
    this.bookService.getBooksByGenre(genreId, currentBookId, 5).subscribe({
      next: (books: Book[]) => {
        this.similarBooks = books.map(book => ({ 
            id: book.id_book,
            title: book.title,
            imageUrl: book.cover_image_url || 'assets/img/placeholder_book.png'
        }));
        this.isLoadingSimilarBooks = false;
        console.log('Similar books loaded:', this.similarBooks);
      },
      error: (err) => {
        console.error('Error loading similar books:', err);
        this.similarBooks = [];
        this.isLoadingSimilarBooks = false;
      }
    });
  }
  checkWishlistStatus(bookId: number): void {
    console.log('Проверка статуса вишлиста для книги ID:', bookId);
    this.isWishlistLoading = true;
    this.bookService.checkWishlistStatus(bookId).subscribe({
      next: (response) => {
        this.isInWishlist = response.isInWishlist;
        this.isWishlistLoading = false;
        console.log('Wishlist status:', this.isInWishlist);
      },
      error: (err) => {
        console.error('Error checking wishlist status:', err);
        this.isWishlistLoading = false;
      }
    });
  }
  getStars(rating: number): string[] {
    rating = Math.max(0, Math.min(5, rating));
    
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const stars: string[] = [];
    
    for (let i = 0; i < fullStars; i++) {
      stars.push('fas fa-star');
    }
    
    if (halfStar) {
      stars.push('fas fa-star-half-alt');
    }
    
    while (stars.length < 5) {
      stars.push('far fa-star');
    }
    
    return stars;
  }
  getAverageBookRating(): number {
    if (!this.reviews || this.reviews.length === 0) {
      return 0;
    }
    
    const totalRating = this.reviews.reduce((sum, review) => {
      return sum + (review.rating || 0);
    }, 0);
    
    return Math.round((totalRating / this.reviews.length) * 10) / 10;
  }
  getAuthorNames(): string {
    if (this.book && this.book.writers && this.book.writers.length > 0) {
      return this.book.writers.map(w => w.name).join(', ');
    }
    return 'Автор неизвестен';
  }
  goBack(): void {
    this.location.back();
  }
  goToProfile(): void {
    this.router.navigate(['/profil']);
  }
  toggleWishlist(): void {
    console.log('toggleWishlist called');
    if (!this.authService.isLoggedIn()) {
      console.log('User not logged in, redirecting to /auth/sign-in...');
      this.router.navigate(['/auth/sign-in'], { queryParams: { returnUrl: this.router.url } });
      return; 
    }
    console.log('User is logged in');
    console.log('Checking bookId and isWishlistLoading. bookId:', this.bookId, 'isLoading:', this.isWishlistLoading);
    if (!this.bookId || this.isWishlistLoading) {
      console.log('Exiting: Invalid bookId or wishlist action already in progress.');
      return;
    }
    console.log('bookId is valid and not currently loading');
    this.isWishlistLoading = true;
    const action = this.isInWishlist 
      ? this.bookService.removeFromWishlist(this.bookId)
      : this.bookService.addToWishlist(this.bookId);
    const successMessage = this.isInWishlist ? 'удалена из' : 'добавлена в';
    console.log('Subscribing to wishlist action...');
    action.subscribe({ 
      next: () => {
    this.isInWishlist = !this.isInWishlist;
        this.isWishlistLoading = false;
        console.log(`Книга ${successMessage} виш-лист`);
      },
      error: (err) => {
        console.error(`Error toggling wishlist for book ${this.bookId}:`, err);
        this.isWishlistLoading = false;
        alert(`Не удалось изменить статус виш-листа. Ошибка: ${err.error?.error || err.message}`);
      }
    });
  }
  showPreviousReview(): void {
    if (this.reviews.length > 0) {
      this.currentReviewIndex = (this.currentReviewIndex - 1 + this.reviews.length) % this.reviews.length;
      this.currentReview = this.reviews[this.currentReviewIndex];
      
      if (this.currentReview) {
        this.checkIfCurrentUserIsAuthor();
        if (this.currentReview.id_user && this.authService.isLoggedIn()) {
          this.checkSubscriptionStatus(this.currentReview.id_user);
        }
      }
    }
  }
  showNextReview(): void {
    if (this.reviews.length > 0) {
      this.currentReviewIndex = (this.currentReviewIndex + 1) % this.reviews.length;
      this.currentReview = this.reviews[this.currentReviewIndex];
      
      if (this.currentReview) {
        this.checkIfCurrentUserIsAuthor();
        if (this.currentReview.id_user && this.authService.isLoggedIn()) {
          this.checkSubscriptionStatus(this.currentReview.id_user);
        }
      }
    }
  }
  scrollSimilarBooks(amount: number): void {
    this.similarBooksGrid.nativeElement.scrollBy({ left: amount, behavior: 'smooth' });
  }
  
  getParagraphs(content: string[]): string[] {
    return content;
  }
  /**
   * Формирует URL для поиска книги на Яндекс.Поиске.
   * @returns Строка с URL или пустая строка, если данных книги нет.
   */
  getYandexSearchUrl(): string {
    if (!this.book) {
      return '';
    }
    const query = `купить ${this.book.title} ${this.getAuthorNames()}`;
    return `https://yandex.ru/search/?text=${encodeURIComponent(query)}`;
  }

  declension(number: number, titles: [string, string, string]): string {
    const cases = [2, 0, 1, 1, 1, 2];
    return titles[(number % 100 > 4 && number % 100 < 20) ? 2 : cases[(number % 10 < 5) ? number % 10 : 5]];
  }
  /**
   * Проверяет, подписан ли текущий пользователь на автора рецензии
   * @param authorId ID автора рецензии
   */
  checkSubscriptionStatus(authorId: number): void {
    if (!authorId || !this.authService.isLoggedIn()) {
      this.isAuthorSubscribed = false;
      return;
    }
    
    this.subscriptionService.checkSubscriptionStatus(authorId).subscribe({
      next: (response) => {
        this.isAuthorSubscribed = response.isSubscribed;
        console.log(`Статус подписки на автора ID ${authorId}:`, this.isAuthorSubscribed);
      },
      error: (err) => {
        console.error('Ошибка проверки статуса подписки:', err);
        this.isAuthorSubscribed = false;
      }
    });
  }
  
  /**
   * Подписка/отписка от автора рецензии
   * @param authorId ID автора рецензии
   */
  subscribeToAuthor(authorId: number): void {
    if (!authorId) return;
    
    if (!this.authService.isLoggedIn()) {
      console.log('Пользователь не авторизован, перенаправление на страницу входа...');
      this.router.navigate(['/auth/sign-in'], { queryParams: { returnUrl: this.router.url } });
      return;
    }
    
    if (this.isAuthorSubscribed) {
      this.subscriptionService.unsubscribeFromUser(authorId).subscribe({
        next: () => {
          this.isAuthorSubscribed = false;
          console.log(`Отписка от автора ID ${authorId} успешна`);
        },
        error: (err) => {
          console.error('Ошибка отписки:', err);
          alert('Не удалось отписаться от автора. Попробуйте позже.');
        }
      });
    } else {
      this.subscriptionService.subscribeToUser(authorId).subscribe({
        next: () => {
          this.isAuthorSubscribed = true;
          console.log(`Подписка на автора ID ${authorId} успешна`);
        },
        error: (err) => {
          console.error('Ошибка подписки:', err);
          alert('Не удалось подписаться на автора. Попробуйте позже.');
        }
      });
    }
  }
  checkIfCurrentUserIsAuthor(): void {
    if (!this.currentReview || !this.currentUserId) {
      this.isCurrentUserAuthor = false;
      return;
    }
    
    this.isCurrentUserAuthor = this.currentReview.id_user === this.currentUserId;
    console.log(`Текущий пользователь ${this.isCurrentUserAuthor ? 'является' : 'не является'} автором рецензии.`);
  }
  
  /**
   * Проверяет, имеет ли пользователь (автор рецензии) статус "Автор"
   * @returns true, если пользователь имеет статус "Автор", иначе false
   */
  isUserAuthorStatus(): boolean {
    if (!this.currentReview || !this.currentReview.user) {
      return false;
    }
    
    if (this.currentReview.user.status === 'Автор') {
      return true;
    }
    
    return false;
  }
}