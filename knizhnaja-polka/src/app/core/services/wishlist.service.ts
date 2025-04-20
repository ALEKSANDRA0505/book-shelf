import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { WishlistItem } from '../models/wishlist-item.model';
@Injectable({
  providedIn: 'root'
})
export class WishlistService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список желаемого для пользователя.
   * @param userId ID пользователя.
   * @returns Observable<WishlistItem[]>
   */
  getWishlist(userId: number): Observable<WishlistItem[]> {
    let params = new HttpParams().set('user_id', userId.toString());
    return this.http.get<WishlistItem[]>(`${this.apiUrl}/wishlist_item_read.php`, { params });
  }
  /**
   * Добавляет книгу в список желаемого.
   * @param itemData Данные { id_user, id_book }.
   * @returns Observable<{ message: string; id_wishlist_item: number; }>
   */
  addToWishlist(itemData: { id_user: number; id_book: number }): Observable<{ message: string; id_wishlist_item: number; }> {
    return this.http.post<{ message: string; id_wishlist_item: number; }>(`${this.apiUrl}/wishlist_item_create.php`, itemData);
  }
  /**
   * Удаляет книгу из списка желаемого.
   * @param userId ID пользователя.
   * @param bookId ID книги.
   * @returns Observable<{ message: string; ... }>
   */
  removeFromWishlist(userId: number, bookId: number): Observable<any> {
    let params = new HttpParams()
      .set('user_id', userId.toString())
      .set('book_id', bookId.toString());
    return this.http.delete<any>(`${this.apiUrl}/wishlist_item_delete.php`, { params });
  }

} 