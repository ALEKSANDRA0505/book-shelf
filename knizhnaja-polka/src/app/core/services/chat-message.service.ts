import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ChatMessage } from '../models/message.model';
@Injectable({
  providedIn: 'root'
})
export class ChatMessageService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает последние сообщения чата.
   * @param limit Максимальное количество сообщений.
   * @param after_id Получить сообщения после этого ID (для подгрузки новых).
   * @returns Observable<ChatMessage[]>
   */
  getMessages(limit: number = 50, after_id?: number): Observable<ChatMessage[]> {
    let params = new HttpParams().set('limit', limit.toString());
    if (after_id) {
      params = params.set('after_id', after_id.toString());
    }
    return this.http.get<ChatMessage[]>(`${this.apiUrl}/chat_messages_read.php`, { params });
  }
  /**
   * Отправляет новое сообщение в чат.
   * @param messageData Данные сообщения (id_user, message_text).
   * @returns Observable<{ message: string; id_message: number; id_user: number; message_text: string; }>
   */
  sendMessage(messageData: { id_user: number; message_text: string }): Observable<{ message: string; id_message: number; id_user: number; message_text: string; }> {
    return this.http.post<{ message: string; id_message: number; id_user: number; message_text: string; }>(`${this.apiUrl}/chat_message_send.php`, messageData);
  }
} 