import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Genre } from '../models/genre.model';
@Injectable({
  providedIn: 'root'
})
export class GenreService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  /**
   * Получает список всех жанров
   * @returns Observable<Genre[]>
   */
  getGenres(): Observable<Genre[]> {
    return this.http.get<Genre[]>(`${this.apiUrl}/genres_read.php`);
  }
  /**
   * Получает жанр по его ID
   * @param id ID жанра
   * @returns Observable<Genre>
   */
  getGenreById(id: number): Observable<Genre> {
    return this.http.get<Genre>(`${this.apiUrl}/genre_read.php?id=${id}`);
  }
  /**
   * Получает жанр по его слагу (slug)
   * @param slug Слаг жанра
   * @returns Observable<Genre>
   */
  getGenreBySlug(slug: string): Observable<Genre> {
    return this.http.get<Genre>(`${this.apiUrl}/genre_read.php?slug=${slug}`);
  }
  createGenre(genreData: { name: string }): Observable<{ message: string; id_genre: number; }> {
    return this.http.post<{ message: string; id_genre: number; }>(`${this.apiUrl}/genre_create.php`, genreData);
  }
  /**
   * Обновляет жанр.
   * @param id ID жанра.
   * @param genreData Данные для обновления (name).
   *                  API ожидает id_genre и name в теле.
   * @returns Observable<{ message: string; id_genre: number; }>
   */
  updateGenre(id: number, genreData: { name: string }): Observable<{ message: string; id_genre: number; }> {
    const payload = { ...genreData, id_genre: id };
    return this.http.put<{ message: string; id_genre: number; }>(`${this.apiUrl}/genre_update.php`, payload);
  }
  deleteGenre(id: number): Observable<{ message: string; id_genre: number; }> {
    return this.http.delete<{ message: string; id_genre: number; }>(`${this.apiUrl}/genre_delete.php?id=${id}`);
  }
} 