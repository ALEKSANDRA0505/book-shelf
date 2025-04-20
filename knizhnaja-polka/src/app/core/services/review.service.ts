import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Review } from '../models/review.model';
@Injectable({
  providedIn: 'root'
})
export class ReviewService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список рецензий.
   * Можно фильтровать по review_id, book_id или user_id.
   * @param filters Объект с фильтрами (id?, book_id?, user_id?)
   * @returns Observable<Review[]>
   */
  getReviews(filters: { id?: number; book_id?: number; user_id?: number } = {}): Observable<Review[]> {
    let params = new HttpParams();
    if (filters.id) {
      params = params.set('id', filters.id.toString());
    }
    if (filters.book_id) {
      params = params.set('book_id', filters.book_id.toString());
    }
    if (filters.user_id) {
      params = params.set('user_id', filters.user_id.toString());
    }
    return this.http.get<Review[]>(`${this.apiUrl}/review_read.php`, { params });
  }
  /**
   * Получает одну рецензию по ID.
   * @param id ID рецензии.
   * @returns Observable<Review>
   */
  getReviewById(id: number): Observable<Review> {
    return this.http.get<Review>(`${this.apiUrl}/review_read.php?id=${id}`);
  }
  /**
   * Создает новую рецензию.
   * @param reviewData Данные рецензии (id_user, id_book, rating, comment?)
   * @returns Observable<{ message: string; id_review: number; }>
   */
  createReview(reviewData: { id_user: number; id_book: number; rating: number; comment?: string | null }): Observable<{ message: string; id_review: number; }> {
    return this.http.post<{ message: string; id_review: number; }>(`${this.apiUrl}/review_create.php`, reviewData);
  }
  /**
   * Обновляет рецензию.
   * @param id ID рецензии для обновления.
   * @param reviewData Данные для обновления (rating, comment?).
   *                   API ожидает id_review, rating, comment? в теле.
   * @returns Observable<{ message: string; id_review: number; }>
   */
  updateReview(id: number, reviewData: { rating: number; comment?: string | null }): Observable<{ message: string; id_review: number; }> {
    const payload = { ...reviewData, id_review: id };
    return this.http.put<{ message: string; id_review: number; }>(`${this.apiUrl}/review_update.php`, payload);
  }
  /**
   * Удаляет рецензию.
   * @param id ID рецензии для удаления.
   * @returns Observable<{ message: string; id_review: number; }>
   */
  deleteReview(id: number): Observable<{ message: string; id_review: number; }> {
    return this.http.delete<{ message: string; id_review: number; }>(`${this.apiUrl}/review_delete.php?id=${id}`);
  }
  /**
   * Отправляет на бэкенд данные для создания новой книги и первой рецензии к ней.
   * Требует аутентификации.
   * @param newBookData Данные новой книги (title, author)
   * @param reviewData Данные рецензии (rating, comment, genre_ids - массив ID)
   * @returns Observable<{ message: string; id_book: number; id_review: number; }>
   */
  submitNewBookWithReview(newBookData: { title: string; author: string }, reviewData: { rating: number; comment?: string | null; genre_ids: number[] }): Observable<{ message: string; id_book: number; id_review: number; }> {
    const payload = {
      ...newBookData,
      ...reviewData
    };
    return this.http.post<{ message: string; id_book: number; id_review: number; }>(`${this.apiUrl}/submit_new_book_review.php`, payload);
  }
  highlightStars(stars: HTMLElement[], rating: number) {
    stars.forEach(star => {
      const starRating = parseInt(star.getAttribute('data-rating') || '0', 10);
      if (starRating <= rating) {
        star.classList.add('active');
      } else {
        star.classList.remove('active');
      }
    });
  }
  resetStars(stars: HTMLElement[]) {
    stars.forEach(star => {
      star.classList.remove('active');
    });
  }
}