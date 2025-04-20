import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute, Router } from '@angular/router';
import { Review } from '../../../core/models/review.model';
import { Book } from '../../../core/models/book.model';
import { User } from '../../../core/models/user.model';
import { ReviewService } from '../../../core/services/review.service';
import { BookService } from '../../../core/services/book.service';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { AuthService } from '../../../core/services/auth.service';
import { Subscription, forkJoin, catchError, of, finalize, tap } from 'rxjs';
import { UserService } from '../../../core/services/user.service';

@Component({
  selector: 'app-review-detail',
  imports: [CommonModule, RouterModule],
  templateUrl: './review-detail.component.html',
  styleUrl: './review-detail.component.css',
  standalone: true
})
export class ReviewDetailComponent implements OnInit, OnDestroy {
  reviewId: number | null = null;
  isLoading: boolean = true;
  error: string | null = null;
  
  review: Review | null = null;
  book: Book | null = null;
  author: User | null = null;
  isAuthorSubscribed: boolean = false;
  isCurrentUserAuthor: boolean = false;
  
  relatedReviews: Review[] = [];
  bookReviews: Review[] = []; // Все рецензии на эту книгу
  isLoadingRelated: boolean = false;
  
  private subscriptions: Subscription[] = [];
  private currentUserId: number | null = null;
  
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private reviewService: ReviewService,
    private bookService: BookService,
    private subscriptionService: SubscriptionService,
    private authService: AuthService,
    private userService: UserService
  ) {
    this.subscriptions.push(
      this.authService.currentUser$.subscribe(user => {
        this.currentUserId = user?.id_user || null;
      })
    );
  }
  
  ngOnInit() {
    console.log('ReviewDetailComponent инициализируется');
    const sub = this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.reviewId = +id;
        this.loadReviewDetails(this.reviewId);
      } else {
        this.error = 'ID рецензии не найден в параметрах';
        this.isLoading = false;
        console.error('ID рецензии не найден в параметрах');
      }
    });
    
    this.subscriptions.push(sub);
  }
  
  ngOnDestroy() {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  loadReviewDetails(id: number): void {
    this.isLoading = true;
    this.error = null;
    
    const sub = this.reviewService.getReviewById(id).pipe(
      catchError(err => {
        this.error = `Не удалось загрузить рецензию: ${err.message || 'Неизвестная ошибка'}`;
        console.error('Ошибка загрузки рецензии:', err);
        return of(null);
      })
    ).subscribe(review => {
      if (!review) {
        this.isLoading = false;
        return;
      }
      
      this.review = review;
      console.log('Загружена рецензия:', review);
      
      this.checkIfCurrentUserIsAuthor();
      
      this.loadAdditionalData(review);
    });
    
    this.subscriptions.push(sub);
  }
  
  checkIfCurrentUserIsAuthor(): void {
    if (!this.review || !this.currentUserId) {
      this.isCurrentUserAuthor = false;
      return;
    }
    
    this.isCurrentUserAuthor = this.review.id_user === this.currentUserId;
    console.log(`Текущий пользователь ${this.isCurrentUserAuthor ? 'является' : 'не является'} автором рецензии.`);
  }
  
  loadAdditionalData(review: Review): void {
    const bookRequest = this.bookService.getBookById(review.id_book).pipe(
      catchError(err => {
        console.error('Ошибка загрузки информации о книге:', err);
        return of(null);
      }),
      tap(book => {
        if (book) {
          this.loadBookReviews(book.id_book);
        }
      })
    );
    
    const userRequest = this.userService.getAuthorById(review.id_user).pipe(
      catchError(err => {
        console.error('Ошибка загрузки информации о пользователе:', err);
        return of(null);
      })
    );
    
    const isLoggedIn = this.authService.isLoggedIn();
    const authorId = review.id_user;
    
    let subscriptionRequest = of({ isSubscribed: false });
    if (isLoggedIn && authorId) {
      subscriptionRequest = this.subscriptionService.checkSubscriptionStatus(authorId).pipe(
        catchError(err => {
          console.error('Ошибка проверки статуса подписки:', err);
          return of({ isSubscribed: false });
        }),
        finalize(() => {
          this.loadRelatedReviews(review.id_user, review.id_review);
        })
      );
    }
    
    const sub = forkJoin({
      book: bookRequest,
      user: userRequest,
      subscriptionStatus: subscriptionRequest
    }).subscribe(results => {
      this.book = results.book;
      
      if (results.user) {
        this.review!.user = results.user;
        console.log('Загружена информация о пользователе:', results.user);
      }
      
      if ('isSubscribed' in results.subscriptionStatus) {
        this.isAuthorSubscribed = results.subscriptionStatus.isSubscribed;
      }
      
      this.isLoading = false;
      
      if (!isLoggedIn) {
        this.loadRelatedReviews(review.id_user, review.id_review);
      }
    });
    
    this.subscriptions.push(sub);
  }
  
  loadRelatedReviews(authorId: number, currentReviewId: number): void {
    if (!authorId) return;
    
    this.isLoadingRelated = true;
    
    const sub = this.reviewService.getReviews({ user_id: authorId }).pipe(
      catchError(err => {
        console.error('Ошибка загрузки связанных рецензий:', err);
        return of([]);
      }),
      finalize(() => this.isLoadingRelated = false)
    ).subscribe(reviews => {
      this.relatedReviews = reviews.filter(r => r.id_review !== currentReviewId).slice(0, 3);
      console.log('Загружены связанные рецензии:', this.relatedReviews);
      
      this.loadBookCoversForRelatedReviews();
    });
    
    this.subscriptions.push(sub);
  }
  
  loadBookCoversForRelatedReviews(): void {
    if (!this.relatedReviews.length) return;
    
    this.relatedReviews.forEach(review => {
      const sub = this.bookService.getBookById(review.id_book).pipe(
        catchError(err => {
          console.error(`Ошибка загрузки обложки для книги ID ${review.id_book}:`, err);
          return of(null);
        })
      ).subscribe(book => {
        if (book) {
          review['book_cover_url'] = book.cover_image_url;
        }
      });
      
      this.subscriptions.push(sub);
    });
  }
  
  getBookCoverUrl(review: any): string {
    return review['book_cover_url'] || 'assets/img/placeholder_book.png';
  }
  
  toggleSubscription(): void {
    if (!this.review) return;
    
    if (!this.authService.isLoggedIn()) {
      this.router.navigate(['/auth/sign-in'], { queryParams: { returnUrl: this.router.url } });
      return;
    }
    
    const authorId = this.review.id_user;
    
    if (this.isAuthorSubscribed) {
      const sub = this.subscriptionService.unsubscribeFromUser(authorId).subscribe({
        next: () => {
          this.isAuthorSubscribed = false;
          console.log(`Отписка от автора ID ${authorId} успешна`);
        },
        error: (err) => {
          console.error('Ошибка отписки:', err);
          alert('Не удалось отписаться от автора. Попробуйте позже.');
        }
      });
      
      this.subscriptions.push(sub);
    } else {
      const sub = this.subscriptionService.subscribeToUser(authorId).subscribe({
        next: () => {
          this.isAuthorSubscribed = true;
          console.log(`Подписка на автора ID ${authorId} успешна`);
        },
        error: (err) => {
          console.error('Ошибка подписки:', err);
          alert('Не удалось подписаться на автора. Попробуйте позже.');
        }
      });
      
      this.subscriptions.push(sub);
    }
  }
  
  getStars(rating: number = 0): string[] {
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
    
    if (rating > 0) {
      console.log('Рейтинг', rating, 'Звезды:', stars);
    }
    
    return stars;
  }
  
  getParagraphs(content: string = ''): string[] {
    if (!content) return [];
    return content.split('</p>').filter(p => p.trim() !== '').map(p => p.replace(/<p>/g, '').trim());
  }
  
  getAuthors(): string {
    if (!this.book || !this.book.writers || !this.book.writers.length) {
      return 'Неизвестный автор';
    }
    return this.book.writers.map(w => w.name).join(', ');
  }
  
  getBookRating(): number {
    if (this.book && typeof this.book.average_rating === 'number' && this.book.average_rating > 0) {
      return Math.round(this.book.average_rating * 10) / 10;
    }
    
    if (this.bookReviews && this.bookReviews.length > 0) {
      const totalRating = this.bookReviews.reduce((sum, review) => {
        return sum + (review.rating || 0);
      }, 0);
      
      return Math.round((totalRating / this.bookReviews.length) * 10) / 10;
    }
    
    if (this.review && this.review.rating) {
      return Math.round(this.review.rating * 10) / 10;
    }
    
    return 0;
  }
  
  getUsername(): string {
    if (!this.review || !this.review.user_username) {
      return 'Аноним';
    }
    return this.review.user_username;
  }
  
  getReviewRating(): number {
    if (!this.review || this.review.rating === undefined || this.review.rating === null) {
      return 0;
    }
    return Math.round(this.review.rating * 10) / 10;
  }
  
  getAvatarUrl(): string {
    if (!this.review || !this.review.profile_picture_url) {
      return 'assets/img/default-avatar.png';
    }
    return this.review.profile_picture_url;
  }
  
  isUserAuthorStatus(): boolean {
    if (!this.review) {
      return false;
    }
    
    if (this.review.user && this.review.user.status) {
      console.log('Статус пользователя:', this.review.user.status);
      return this.review.user.status === 'Автор';
    }
    
    return false;
  }
  
  loadBookReviews(bookId: number): void {
    if (!bookId) return;
    
    const sub = this.bookService.getReviewsForBook(bookId).pipe(
      catchError(err => {
        console.error('Ошибка загрузки рецензий книги:', err);
        return of([]);
      })
    ).subscribe(reviews => {
      this.bookReviews = reviews;
      console.log(`Загружено ${this.bookReviews.length} рецензий для книги ID ${bookId}`);
    });
    
    this.subscriptions.push(sub);
  }
}
