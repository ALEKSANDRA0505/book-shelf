import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Book } from '../models/book.model';
import { Review } from '../models/review.model';
@Injectable({
  providedIn: 'root'
})
export class BookService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список всех книг
   * @returns Observable<Book[]>
   */
  getBooks(): Observable<Book[]> {
    return this.http.get<Book[]>(`${this.apiUrl}/books_read.php`);
  }
  /**
   * Получает список популярных книг (топ-10 по рейтингу/рецензиям)
   * @returns Observable<Book[]>
   */
  getPopularBooks(): Observable<Book[]> {
    return this.http.get<Book[]>(`${this.apiUrl}/books_read.php?popular=true`);
  }
  /**
   * Получает книгу по её ID
   * @param id ID книги
   * @returns Observable<Book>
   */
  getBookById(id: number): Observable<Book> {
    return this.http.get<Book>(`${this.apiUrl}/book_read.php?id=${id}`);
  }

  /**
   * Создает новую книгу.
   * @param bookData Данные книги (должны соответствовать ожиданиям book_create.php)
   *             Ожидает: title (string, required), description (string|null),
   *             cover_image_url (string|null), genre_ids (number[]), writer_ids (number[])
   * @returns Observable<{ message: string; id_book: number; }>
   */
  createBook(bookData: Partial<Book> & { genre_ids?: number[], writer_ids?: number[] }): Observable<{ message: string; id_book: number; }> {
    return this.http.post<{ message: string; id_book: number; }>(`${this.apiUrl}/book_create.php`, bookData);
  }
  /**
   * Обновляет книгу.
   * @param id ID книги для обновления.
   * @param bookData Данные для обновления (должны соответствовать ожиданиям book_update.php)
   *             Требуется id_book в теле запроса!
   *             Ожидает: id_book (number, required), title (string, required), description (string|null),
   *             cover_image_url (string|null), genre_ids (number[]), writer_ids (number[])
   * @returns Observable<{ message: string; id_book: number; }>
   */
  updateBook(id: number, bookData: Partial<Book> & { id_book: number, genre_ids?: number[], writer_ids?: number[] }): Observable<{ message: string; id_book: number; }> {
    if (!bookData.id_book) bookData.id_book = id;
    return this.http.put<{ message: string; id_book: number; }>(`${this.apiUrl}/book_update.php`, bookData);
  }
  /**
   * Удаляет книгу.
   * @param id ID книги для удаления.
   * @returns Observable<{ message: string; id_book: number; }>
   */
  deleteBook(id: number): Observable<{ message: string; id_book: number; }> {
    return this.http.delete<{ message: string; id_book: number; }>(`${this.apiUrl}/book_delete.php?id=${id}`);
  }
  /**
   * Получает список рецензий для книги.
   * @param bookId ID книги.
   * @returns Observable<Review[]>
   */
  getReviewsForBook(bookId: number): Observable<Review[]> {
    return this.http.get<Review[]>(`${this.apiUrl}/review_read.php?book_id=${bookId}`);
  }
  /**
   * Проверяет, находится ли книга в виш-листе текущего пользователя.
   * Требует аутентификации.
   * @param bookId ID книги.
   * @returns Observable<{ isInWishlist: boolean }>
   */
  checkWishlistStatus(bookId: number): Observable<{ isInWishlist: boolean }> {
    return this.http.get<{ isInWishlist: boolean }>(`${this.apiUrl}/wishlist_status.php?id_book=${bookId}`);
  }
  /**
   * Добавляет книгу в виш-лист.
   * Требует аутентификации.
   * @param bookId ID книги.
   * @returns Observable<any>
   */
  addToWishlist(bookId: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/wishlist_add.php`, { id_book: bookId });
  }
  /**
   * Удаляет книгу из виш-листа.
   * Требует аутентификации.
   * @param bookId ID книги.
   * @returns Observable<any>
   */
  removeFromWishlist(bookId: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/wishlist_remove.php?id_book=${bookId}`);
  }
  /**
   * Получает список книг по ID жанра, исключая указанную книгу.
   * @param genreId ID жанра.
   * @param excludeBookId ID книги, которую нужно исключить.
   * @param limit Максимальное количество книг для возврата.
   * @returns Observable<Book[]>
   */
  getBooksByGenre(genreId: number, excludeBookId: number, limit: number = 10): Observable<Book[]> {
    let params = new HttpParams()
      .set('genre_id', genreId.toString())
      .set('exclude_book_id', excludeBookId.toString())
      .set('limit', limit.toString());
    return this.http.get<Book[]>(`${this.apiUrl}/books_by_genre_read.php`, { params });
  }
  /**
   * Проверяет, существует ли книга с указанным названием и автором.
   * @param title Название книги.
   * @param author Имя автора.
   * @returns Observable<{ exists: boolean }>
   */
  checkBookExists(title: string, author: string): Observable<{ exists: boolean }> {
    const params = new HttpParams()
      .set('title', title)
      .set('author', author);
    return this.http.get<{ exists: boolean }>(`${this.apiUrl}/check_book_exists.php`, { params });
  }
}
