import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { BookService } from '../../core/services/book.service';
import { Book } from '../../core/models/book.model';
import { Writer } from '../../core/models/writer.model';
import { Subscription } from 'rxjs';
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterModule],
  template: `
    <div class="home-container">
      <h1>Добро пожаловать на Книжную полку!</h1>
      
      <div class="popular-books-section">
        <h2>Популярные книги</h2>
        <div *ngIf="isLoading" class="loading-indicator">Загрузка популярных книг...</div>
        <div *ngIf="!isLoading && errorMessage" class="error-message">{{ errorMessage }}</div>
        <div *ngIf="!isLoading && !errorMessage && popularBooks.length === 0" class="info-message">Нет популярных книг для отображения.</div>
        
        <div class="books-grid" *ngIf="!isLoading && !errorMessage && popularBooks.length > 0">
          <a *ngFor="let book of popularBooks" 
             [routerLink]="['/book-detail']" [queryParams]="{id: book.id_book}"
             class="book-card">
              <img [src]="book.cover_image_url || 'assets/img/placeholder.jpg'" [alt]="book.title" class="book-cover">
              <div class="book-info">
                <h3 class="book-title">{{ book.title }}</h3>
                <p class="book-author">{{ getWritersString(book.writers) }}</p>
                <div class="book-rating">
                  <ng-container *ngIf="book.average_rating !== null && book.average_rating !== undefined">
                      <i *ngFor="let star of getStars(book.average_rating)" [ngClass]="star" style="color: #f0ad4e;"></i>
                      <span style="margin-left: 5px; color: #6c757d; font-size: 0.9em;">({{ (book.average_rating || 0) | number:'1.1-1' }})</span>
                  </ng-container>
                  <ng-container *ngIf="book.average_rating === null || book.average_rating === undefined">
                      <span style="color: #6c757d; font-size: 0.9em;">Нет оценок</span>
                  </ng-container>
                </div>
              </div>
          </a>
        </div>
      </div>
      <div class="features">
        <div class="feature">
          <h2>Обширная база книг</h2>
          <p>Просматривайте и добавляйте книги в свои списки</p>
          <a routerLink="/books" class="feature-link">Перейти к книгам</a>
        </div>
        <div class="feature">
          <h2>Чат-рекомендации</h2>
          <p>Получайте персонализированные рекомендации по книгам</p>
          <a routerLink="/chat" class="feature-link">Открыть чат</a>
        </div>
        <div class="feature">
          <h2>Авторы рецензий</h2>
          <p>Подписывайтесь на интересных авторов рецензий</p>
          <a routerLink="/authors" class="feature-link">Смотреть авторов</a>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .home-container {
      padding: 40px 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    h1, h2 {
      text-align: center;
      margin-bottom: 40px;
      color: #333;
    }
    .popular-books-section {
      margin-bottom: 60px;
    }
    .books-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 25px;
      justify-content: center;
    }
    .book-card {
      display: flex;
      flex-direction: column;
      background-color: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      text-decoration: none;
      color: inherit;
    }
    .book-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    .book-cover {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }
    .book-info {
      padding: 15px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }
    .book-title {
      font-size: 1em;
      font-weight: 600;
      margin: 0 0 5px 0;
      color: #333;
      display: -webkit-box;
      -webkit-line-clamp: 2; 
      -webkit-box-orient: vertical;  
      overflow: hidden;
      text-overflow: ellipsis;
      min-height: 2.4em;
    }
    .book-author {
      font-size: 0.9em;
      color: #666;
      margin: 0 0 10px 0;
      display: -webkit-box;
      -webkit-line-clamp: 1; 
      -webkit-box-orient: vertical;  
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .book-rating {
      margin-top: auto;
      font-size: 0.9em;
    }
    .loading-indicator, .error-message, .info-message {
        text-align: center;
        padding: 20px;
        font-size: 1.1em;
        color: #555;
    }
    .error-message { color: #dc3545; }
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }
    .feature {
      padding: 20px;
      border-radius: 8px;
      background-color: #f5f5f5;
      text-align: center;
      transition: transform 0.3s ease;
    }
    .feature:hover {
      transform: translateY(-5px);
    }
    .feature h2 {
      color: #2c3e50;
      margin-bottom: 15px;
    }
    .feature p {
      color: #666;
      margin-bottom: 20px;
    }
    .feature-link {
      display: inline-block;
      padding: 10px 20px;
      background-color: #3498db;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }
    .feature-link:hover {
      background-color: #2980b9;
    }
  `]
})
export class HomeComponent implements OnInit {
  popularBooks: Book[] = [];
  isLoading: boolean = false;
  errorMessage: string | null = null;
  private booksSubscription: Subscription | null = null;
  constructor(private bookService: BookService) {}
  ngOnInit(): void {
    this.loadPopularBooks();
  }
  ngOnDestroy(): void {
    this.booksSubscription?.unsubscribe();
  }
  loadPopularBooks(): void {
    this.isLoading = true;
    this.errorMessage = null;
    this.popularBooks = [];
    this.booksSubscription = this.bookService.getPopularBooks().subscribe({
      next: (books) => {
        this.popularBooks = books;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Ошибка загрузки популярных книг:', error);
        this.errorMessage = 'Не удалось загрузить популярные книги. Пожалуйста, попробуйте позже.';
        this.isLoading = false;
      }
    });
  }
  getStars(rating: number | null | undefined): string[] {
    rating = rating ?? 0;
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const stars: string[] = [];
    for (let i = 0; i < fullStars; i++) stars.push('fas fa-star');
    if (halfStar) stars.push('fas fa-star-half-alt');
    while (stars.length < 5) stars.push('far fa-star');
    return stars;
  }
  getWritersString(writers: Writer[] | undefined): string {
    if (!writers || writers.length === 0) {
      return 'Автор неизвестен';
    }
    const writerNames = writers.map(w => w.name);
    if (writerNames.length > 2) {
      return writerNames.slice(0, 2).join(', ') + ' и др.';
    }
    return writerNames.join(', ');
  }
} 