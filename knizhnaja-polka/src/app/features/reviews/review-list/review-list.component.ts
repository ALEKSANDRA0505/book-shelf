import { Component, OnInit } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Review } from '../../../core/models/review.model';
import { Book } from '../../../core/models/book.model';
import { Genre } from '../../../core/models/genre.model';
import { BookService } from '../../../core/services/book.service';
import { GenreService } from '../../../core/services/genre.service';
import { ReviewService } from '../../../core/services/review.service';
import { AuthService } from '../../../core/services/auth.service';
import { catchError, finalize, switchMap, tap } from 'rxjs/operators';
import { of, throwError } from 'rxjs';
@Component({
  selector: 'app-review-list',
  templateUrl: './review-list.component.html',
  styleUrls: ['./review-list.component.css'],
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule]
})
export class ReviewListComponent implements OnInit {
  searchTerm: string = '';
  bookTitle: string = '';
  bookAuthor: string = '';
  selectedRating: number = 0;
  hoveredRating: number = 0;
  selectedGenreIds: number[] = [];
  reviewText: string = '';
  genres: Genre[] = [];
  allBooks: Book[] = [];
  selectedBookId: number | null = null;
  isNewBook: boolean = false;
  isLoadingBooks: boolean = false;
  isLoadingGenres: boolean = false;
  isSubmitting: boolean = false;
  errorMessage: string | null = null;
  successMessage: string | null = null;
  currentUserId: number | null = null;
  constructor(
    private router: Router,
    private bookService: BookService,
    private genreService: GenreService,
    private reviewService: ReviewService,
    private authService: AuthService
  ) {}
  ngOnInit(): void {
    this.loadGenres();
    this.loadBooks();
    this.authService.currentUser$.subscribe(user => {
      this.currentUserId = user ? user.id_user : null;
    });
  }
  loadGenres(): void {
    console.log("Загрузка списка жанров (из сервиса)... ");
    this.isLoadingGenres = true;
    this.errorMessage = null;
    this.genreService.getGenres()
      .pipe(
        catchError(err => {
          console.error('Ошибка загрузки жанров:', err);
          this.errorMessage = 'Не удалось загрузить жанры.';
          return of([]);
        }),
        finalize(() => this.isLoadingGenres = false)
      )
      .subscribe(genres => {
        this.genres = genres;
        this.selectedGenreIds = [];
      });
  }
  loadBooks(): void {
    console.log("Загрузка списка книг (из сервиса)... ");
    this.isLoadingBooks = true;
    this.errorMessage = null;
    this.bookService.getBooks()
      .pipe(
        catchError(err => {
          console.error('Ошибка загрузки книг:', err);
          this.errorMessage = 'Не удалось загрузить список книг.';
          return of([]);
        }),
        finalize(() => this.isLoadingBooks = false)
      )
      .subscribe(books => {
        console.log('Загруженные книги:', books);
        this.allBooks = books;
        this.selectedBookId = null;
        this.bookTitle = '';
        this.bookAuthor = '';
      });
  }
  onBookSelectionChange(): void {
    this.errorMessage = null;
    console.log('Выбран ID книги:', this.selectedBookId);
    if (!this.isNewBook && this.selectedBookId !== null) {
      const selectedBook = this.allBooks.find(book => book.id_book === this.selectedBookId);
      console.log('Найденная книга:', selectedBook);
      if (selectedBook) {
        console.log('Авторы найденной книги (строка):', selectedBook.author_string);
        this.bookTitle = selectedBook.title;
        this.bookAuthor = selectedBook.author_string ?? '';
        console.log('Присвоенный автор:', this.bookAuthor);
      } else {
        this.bookTitle = '';
        this.bookAuthor = '';
      }
    } else if (this.selectedBookId === null) {
      this.bookTitle = '';
      this.bookAuthor = '';
    }
  }
  onNewBookToggle(): void {
    this.errorMessage = null;
    console.log('New book toggle:', this.isNewBook);
    this.selectedBookId = null;
    this.bookTitle = '';
    this.bookAuthor = '';
    this.selectedGenreIds = [];
  }
  highlightStars(rating: number): void {
    this.hoveredRating = rating;
  }
  resetStars(): void {
    this.hoveredRating = 0;
  }
  setRating(rating: number): void {
    this.selectedRating = rating;
  }
  publishReview(): void {
    this.errorMessage = null;
    this.successMessage = null;
    if (!this.currentUserId) {
      this.errorMessage = 'Для публикации рецензии необходимо войти в систему.';
      return;
    }
    this.isSubmitting = true;
    let bookData: Partial<Book>;
    let reviewPayload: any;
    let needsModeration = false;
    if (this.isNewBook) {
      if (!this.bookTitle || !this.bookAuthor || this.selectedRating === 0 || this.selectedGenreIds.length === 0 || !this.reviewText) {
        this.errorMessage = 'Новая книга: Пожалуйста, заполните название, автора, рейтинг, выберите хотя бы один жанр и напишите текст рецензии.';
        this.isSubmitting = false;
        return;
      }
      console.log('Проверка существования книги перед отправкой...');
      this.bookService.checkBookExists(this.bookTitle, this.bookAuthor).pipe(
        switchMap(checkResponse => {
          if (checkResponse.exists) {
            this.errorMessage = 'Книга с таким названием и автором уже существует. Выберите ее из списка.';
            return throwError(() => new Error('Book already exists'));
          } else {
            this.successMessage = 'Добавление новой книги пока не доступно. Выберите книгу из списка или дождитесь обновления.';
            this.resetForm();
            return of(null);
          }
        }),
        catchError(err => {
          if (err.message !== 'Book already exists' && err.message !== 'New book addition disabled') {
             console.error('Ошибка проверки или добавления новой книги и рецензии:', err);
             this.errorMessage = err?.error?.error || err?.message || 'Произошла ошибка.';
          }
          return throwError(() => err);
        }),
        finalize(() => this.isSubmitting = false)
      ).subscribe(response => {
         console.log('Subscribe block reached (should only happen if logic changes):', response);
      });
    } else {
      if (this.selectedBookId === null || this.selectedRating === 0 || !this.reviewText) {
        this.errorMessage = 'Существующая книга: Пожалуйста, выберите книгу, рейтинг и напишите текст рецензии.';
        this.isSubmitting = false;
        return;
      }
      console.log('Готовим рецензию для существующей книги к публикации.');
      reviewPayload = {
        id_user: this.currentUserId,
        id_book: this.selectedBookId,
        rating: this.selectedRating,
        comment: this.reviewText
      };
      this.reviewService.createReview(reviewPayload)
        .pipe(
          catchError(err => {
            console.error('Ошибка публикации рецензии:', err);
            this.errorMessage = err?.error?.error || err?.message || 'Не удалось опубликовать рецензию.';
            return throwError(() => err);
          }),
          finalize(() => this.isSubmitting = false)
        )
        .subscribe(response => {
          console.log('Рецензия успешно опубликована:', response);
          this.successMessage = response.message || 'Рецензия успешно опубликована!';
          this.resetForm();
          console.log('Переход на страницу книги, ID книги:', this.selectedBookId);
          if (this.selectedBookId) {
            const bookId = Number(this.selectedBookId);
            this.router.navigate(['/book-detail'], { 
              queryParams: { id: bookId },
              queryParamsHandling: 'merge'
            });
          } else {
            console.error('ID книги отсутствует, переход на страницу книги невозможен');
          }
        });
    }
  }
  resetForm(): void {
    this.isNewBook = false;
    this.selectedBookId = null;
    this.bookTitle = '';
    this.bookAuthor = '';
    this.selectedRating = 0;
    this.selectedGenreIds = [];
    this.reviewText = '';
    this.errorMessage = null;
  }
}