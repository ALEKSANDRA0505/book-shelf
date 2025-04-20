import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
import { Book } from '../models/book.model';
import { Review } from '../models/review.model';
import { Subscription as UserSubscriptionModel } from '../models/subscription.model';
@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список всех пользователей (только для админа)
   */
  getUsers(): Observable<User[]> {
    return this.http.get<User[]>(`${this.apiUrl}/users_read.php`);
  }
  /**
   * Получает список всех пользователей или одного пользователя по ID.
   * @param id (Опционально) ID пользователя.
   * @returns Observable<User | User[]>
   */
  getUsersById(id?: number): Observable<User | User[]> {
    if (id) {
      return this.http.get<User>(`${this.apiUrl}/user_profile_read.php?id=${id}`);
    } else {
      return this.http.get<User[]>(`${this.apiUrl}/user_profile_read.php`);
    }
  }
  /**
   * Обновляет профиль пользователя.
   * Отправляет POST запрос на user_profile_update.php.
   * @param userData Данные пользователя для обновления (должны включать id_user).
   * @returns Observable с ответом от сервера.
   */
  updateUserProfile(userData: Partial<User>): Observable<any> {
    if (!userData.id_user) {
      throw new Error('User ID is required to update profile');
    }
    return this.http.post<any>(`${this.apiUrl}/user_profile_update.php`, userData);
  }
  /**
   * Обновляет настройки пользователя (например, цель по чтению).
   * Отправляет POST запрос на user_settings_update.php.
   * @param settingsData Данные настроек для обновления (должны включать id_user, reading_goal и опционально read_books_count).
   * @returns Observable с ответом от сервера.
   */
  updateUserSettings(settingsData: { id_user: number, reading_goal: number, read_books_count?: number }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/user_settings_update.php`, settingsData);
  }
  /**
   * Добавляет прочитанную книгу для текущего пользователя.
   * Отправляет POST запрос на read_books_add.php.
   * @param bookData Объект с title и author книги.
   * @returns Observable с данными добавленной книги.
   */
  addReadBook(bookData: { title: string, author: string }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/read_books_add.php`, bookData);
  }
  /**
   * Получает список прочитанных книг для текущего пользователя.
   * Отправляет GET запрос на read_books_get.php.
   * @returns Observable<any[]> Массив прочитанных книг.
   */
  getReadBooks(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/read_books_get.php`);
  }
  /**
   * Удаляет профиль пользователя.
   * @param id ID пользователя.
   * @returns Observable<{ message: string; id_user: number; }>
   */
  deleteUserProfile(id: number): Observable<{ message: string; id_user: number; }> {
    return this.http.delete<{ message: string; id_user: number; }>(`${this.apiUrl}/user_profile_delete.php?id=${id}`);
  }
  /**
   * Загружает новый аватар пользователя.
   * Отправляет POST запрос с FormData на user_profile_picture_update.php.
   * @param file Выбранный файл изображения.
   * @returns Observable с ответом от сервера (предположительно, { profile_picture_url: string }).
   */
  updateProfilePicture(file: File): Observable<{ profile_picture_url: string }> {
    const formData = new FormData();
    formData.append('avatar', file, file.name);
    return this.http.post<{ profile_picture_url: string }>(`${this.apiUrl}/user_profile_picture_update.php`, formData);
  }

  /**
   * Получает список книг из виш-листа текущего пользователя.
   * Требует аутентификации.
   * @returns Observable<Book[]>
   */
  getWishlist(): Observable<Book[]> {
    return this.http.get<Book[]>(`${this.apiUrl}/wishlist_read.php`);
  }
  /**
   * Получает список пользователей со статусом 'Автор'.
   * @returns Observable<User[]>
   */
  getAuthors(): Observable<User[]> {
    return this.http.get<User[]>(`${this.apiUrl}/authors_read.php`);
  }
  /**
   * Получает одного пользователя со статусом 'Автор' по ID.
   * @param id ID пользователя.
   * @returns Observable<User>
   */
  getAuthorById(id: number): Observable<User> {
    return this.http.get<User>(`${this.apiUrl}/authors_read.php?id=${id}`);
  }
  /**
   * Получает список рецензий для указанного пользователя.
   * @param userId ID пользователя.
   * @returns Observable<Review[]>
   */
  getReviewsByUserId(userId: number): Observable<Review[]> {
    return this.http.get<Review[]>(`${this.apiUrl}/review_read.php?user_id=${userId}`);
  }
  /**
   * Получает список подписок текущего пользователя (тех, на кого он подписан).
   * Требует аутентификации.
   * @returns Observable<UserSubscriptionModel[]>
   */
  getSubscriptions(): Observable<UserSubscriptionModel[]> {
    return this.http.get<UserSubscriptionModel[]>(`${this.apiUrl}/subscriptions_read.php`);
  }
  /**
   * Удаляет пользователя по ID (только для админов).
   * @param id ID пользователя для удаления
   * @returns Observable<any>
   */
  deleteUser(id: number): Observable<any> {
    return this.http.delete<any>(`${this.apiUrl}/user_profile_delete.php?id=${id}`);
  }
  /**
   * Обновляет данные пользователя по ID (только для админов).
   * @param id ID пользователя для обновления
   * @param userData Объект с обновляемыми данными пользователя (включая id_user).
   *                 Может содержать поля: username, email, status, city, age, about_me, reading_goal, profile_picture_url, password (для смены пароля)
   * @returns Observable<any>
   */
  updateUser(id: number, userData: Partial<User & { password?: string }>): Observable<any> {
    const dataToSend = { ...userData, id_user: id }; 
    return this.http.post<any>(`${this.apiUrl}/user_profile_update.php`, dataToSend);
  }
  /**
   * Создает нового пользователя (только для админов).
   * @param userData Данные нового пользователя (username, email, password, status обязательны).
   * @returns Observable<any> Ответ от API (например, { message: string, id_user: number })
   */
  createUser(userData: Partial<User>): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/user_create_admin.php`, userData);
  }
  /*
  updateUser(userData: Partial<User>): Observable<any> {
  }
  */
}
