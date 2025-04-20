import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Subscription } from '../models/subscription.model';
@Injectable({
  providedIn: 'root'
})
export class SubscriptionService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список пользователей, на которых подписан данный пользователь.
   * @param userId ID пользователя-подписчика.
   * @returns Observable<Subscription[]>
   */
  getSubscriptions(userId: number): Observable<Subscription[]> {
    let params = new HttpParams().set('user_id', userId.toString());
    return this.http.get<Subscription[]>(`${this.apiUrl}/subscriptions_read.php`, { params });
  }
  /**
   * Получает список подписчиков данного пользователя.
   * @param userId ID пользователя, чьих подписчиков получаем.
   * @returns Observable<Subscription[]>
   */
  getSubscribers(userId: number): Observable<Subscription[]> {
    let params = new HttpParams().set('user_id', userId.toString());
    return this.http.get<Subscription[]>(`${this.apiUrl}/subscribers_read.php`, { params });
  }
  /**
   * Создает новую подписку.
   * @param data Данные подписки { subscriber_user_id, target_user_id }.
   * @returns Observable<{ message: string; id_subscription: number; ... }>
   */
  subscribe(data: { subscriber_user_id: number; target_user_id: number }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/subscribe.php`, data);
  }
  /**
   * Удаляет подписку.
   * @param data Данные подписки { subscriber_user_id, target_user_id }.
   * @returns Observable<any>
   */
  unsubscribe(data: { subscriber_user_id: number; target_user_id: number }): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/unsubscribe.php`, data);
  }
  /**
   * Подписывается на пользователя.
   * @param userIdToFollow ID пользователя, на которого подписываемся.
   * @returns Observable<any>
   */
  subscribeToUser(userIdToFollow: number): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/subscription_add.php`, { id_following_user: userIdToFollow });
  }
  /**
   * Отписывается от пользователя.
   * @param userIdToUnfollow ID пользователя, от которого отписываемся.
   * @returns Observable<any>
   */
  unsubscribeFromUser(userIdToUnfollow: number): Observable<any> {
    const params = new HttpParams().set('id_following_user', userIdToUnfollow.toString());
    return this.http.delete<any>(`${this.apiUrl}/subscription_remove.php`, { params });
  }
  /**
   * Проверяет, подписан ли текущий пользователь на указанного пользователя.
   * @param userIdToCheck ID пользователя, для которого проверяется статус подписки.
   * @returns Observable<{ isSubscribed: boolean }>
   */
  checkSubscriptionStatus(userIdToCheck: number): Observable<{ isSubscribed: boolean }> {
    const params = new HttpParams().set('id_following_user', userIdToCheck.toString());
    return this.http.get<{ isSubscribed: boolean }>(`${this.apiUrl}/subscription_status.php`, { params });
  }
} 