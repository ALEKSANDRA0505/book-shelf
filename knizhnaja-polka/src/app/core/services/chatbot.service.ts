import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
export interface ChatbotResponse {
  status: 'success' | 'error';
  message: string;
}
@Injectable({
  providedIn: 'root'
})
export class ChatbotService {
  private apiUrl = `${environment.apiUrl}/chatbot.php`;
  constructor(private http: HttpClient) { }
  sendMessage(message: string): Observable<ChatbotResponse> {
    return this.http.post<ChatbotResponse>(this.apiUrl, { message });
  }
} 