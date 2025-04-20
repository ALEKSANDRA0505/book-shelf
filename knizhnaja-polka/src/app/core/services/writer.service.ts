import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Writer } from '../models/writer.model';
import { Book } from '../models/book.model';
@Injectable({
  providedIn: 'root'
})
export class WriterService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список всех писателей (требует прав админа).
   * @returns Observable<Writer[]>
   */
  getWriters(): Observable<Writer[]> {
    return this.http.get<Writer[]>(`${this.apiUrl}/writers_read.php`);
  }
  /**
   * Получает одного писателя по ID (требует прав админа).
   * @param id ID писателя
   * @returns Observable<Writer>
   */
  getWriterById(id: number): Observable<Writer> {
    return this.http.get<Writer>(`${this.apiUrl}/writers_read.php?id=${id}`);
  }
  /**
   * Получает список книг для конкретного писателя.
   * @param writerId ID писателя.
   * @returns Observable<Book[]>
   */
  getBooksByWriterId(writerId: number): Observable<Book[]> {
    return this.http.get<Book[]>(`${this.apiUrl}/books_by_writer_read.php?writer_id=${writerId}`);
  }
  /**
   * Создает нового писателя.
   * @param writerData Данные писателя (name, biography?, photo_url?).
   * @returns Observable<{ message: string; id_writer: number; }>
   */
  createWriter(writerData: { name: string; biography?: string | null; photo_url?: string | null }): Observable<{ message: string; id_writer: number; }> {
    return this.http.post<{ message: string; id_writer: number; }>(`${this.apiUrl}/writer_create.php`, writerData);
  }
  /**
   * Обновляет данные писателя.
   * @param id ID писателя.
   * @param writerData Данные для обновления (name, biography?, photo_url?).
   *                   API ожидает id_writer в теле.
   * @returns Observable<{ message: string; id_writer: number; }>
   */
  updateWriter(id: number, writerData: { name: string; biography?: string | null; photo_url?: string | null }): Observable<{ message: string; id_writer: number; }> {
    const payload = { ...writerData, id_writer: id };
    return this.http.put<{ message: string; id_writer: number; }>(`${this.apiUrl}/writer_update.php`, payload);
  }
  /**
   * Удаляет писателя.
   * @param id ID писателя.
   * @returns Observable<{ message: string; id_writer: number; }>
   */
  deleteWriter(id: number): Observable<{ message: string; id_writer: number; }> {
    return this.http.delete<{ message: string; id_writer: number; }>(`${this.apiUrl}/writer_delete.php?id=${id}`);
  }
} 