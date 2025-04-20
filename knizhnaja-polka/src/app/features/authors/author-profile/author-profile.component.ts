import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterModule, Router } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { UserService } from '../../../core/services/user.service';
import { User } from '../../../core/models/user.model';
import { Review } from '../../../core/models/review.model';
import { AuthService } from '../../../core/services/auth.service';
import { SubscriptionService } from '../../../core/services/subscription.service';
import { Subscription } from 'rxjs';
@Component({
  selector: 'app-author-profile',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule],
  templateUrl: './author-profile.component.html',
  styleUrls: ['./author-profile.component.css']
})
export class AuthorProfileComponent implements OnInit {
  author: User | null = null;
  reviews: Review[] = [];
  isSubscribed: boolean = false;
  isLoadingSubscriptionStatus: boolean = false;
  isTogglingSubscription: boolean = false;
  isLoading: boolean = true;
  isLoadingReviews: boolean = false;
  error: string | null = null;
  reviewsError: string | null = null;
  subscriptionError: string | null = null;
  authorId: number | null = null;
  private authSubscription: Subscription | null = null;
  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private userService: UserService,
    private authService: AuthService,
    private subscriptionService: SubscriptionService
  ) { }
  ngOnInit(): void {
    this.route.queryParamMap.subscribe(params => {
      const id = params.get('id');
      if (id) {
        this.authorId = +id;
        this.loadAuthorProfile(this.authorId);
        this.loadAuthorReviews(this.authorId);
        this.authSubscription = this.authService.isLoggedIn$.subscribe(isLoggedIn => {
          if (isLoggedIn && this.authorId) {
            this.checkSubscriptionStatus(this.authorId);
          } else {
            this.isSubscribed = false;
          }
        });
      } else {
        this.error = 'ID автора не найден в параметрах URL.';
        this.isLoading = false;
      }
    });
  }
  ngOnDestroy(): void {
    if (this.authSubscription) {
      this.authSubscription.unsubscribe();
    }
  }
  loadAuthorProfile(id: number): void {
    this.isLoading = true;
    this.error = null;
    this.userService.getAuthorById(id).subscribe({
      next: (user: User) => {
        this.author = user;
        this.isLoading = false;
        console.log('Author profile loaded:', this.author);
      },
      error: (err) => {
        console.error(`Error loading author profile for ID ${id}:`, err);
        if (err.status === 404) {
            this.error = 'Автор с таким ID не найден.';
        } else {
            this.error = 'Не удалось загрузить профиль автора.';
        }
        this.isLoading = false;
      }
    });
  }
  loadAuthorReviews(userId: number): void {
    console.log('Loading reviews for author ID:', userId);
    this.isLoadingReviews = true;
    this.reviewsError = null;
    this.reviews = [];
    this.userService.getReviewsByUserId(userId).subscribe({
        next: (reviewsData: Review[]) => {
            this.reviews = reviewsData;
            this.isLoadingReviews = false;
            console.log('Author reviews loaded:', this.reviews);
        },
        error: (err) => {
            console.error(`Error loading reviews for author ID ${userId}:`, err);
            this.reviewsError = 'Не удалось загрузить рецензии автора.';
            this.isLoadingReviews = false;
        }
    });
  }
  checkSubscriptionStatus(authorId: number): void {
    console.log('Checking subscription status for author ID:', authorId);
    this.isLoadingSubscriptionStatus = true;
    this.subscriptionError = null;
    this.subscriptionService.checkSubscriptionStatus(authorId).subscribe({
      next: (response) => {
        this.isSubscribed = response.isSubscribed;
        this.isLoadingSubscriptionStatus = false;
        console.log('Subscription status:', this.isSubscribed);
      },
      error: (err) => {
        console.error('Error checking subscription status:', err);
        this.isSubscribed = false;
        this.isLoadingSubscriptionStatus = false;
      }
    });
  }
  toggleSubscribe(): void {
    if (!this.authorId) return;
    if (!this.authService.isLoggedIn()) {
      console.log('User not logged in, redirecting to login...');
      this.router.navigate(['/auth/sign-in'], { queryParams: { returnUrl: this.router.url } });
      return;
    }
    if (this.isTogglingSubscription) return;
    this.isTogglingSubscription = true;
    this.subscriptionError = null;
    const action = this.isSubscribed
      ? this.subscriptionService.unsubscribeFromUser(this.authorId)
      : this.subscriptionService.subscribeToUser(this.authorId);
    action.subscribe({
      next: () => {
        this.isSubscribed = !this.isSubscribed;
        this.isTogglingSubscription = false;
        console.log(`Successfully ${this.isSubscribed ? 'subscribed to' : 'unsubscribed from'} author ID:`, this.authorId);
      },
      error: (err) => {
        console.error(`Error toggling subscription for author ID ${this.authorId}:`, err);
        this.subscriptionError = `Не удалось ${this.isSubscribed ? 'отписаться' : 'подписаться'}. Ошибка: ${err.error?.error || err.message}`;
        this.isTogglingSubscription = false;
      }
    });
  }
}
