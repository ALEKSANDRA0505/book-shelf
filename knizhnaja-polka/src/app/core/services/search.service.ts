import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Book } from '../models/book.model';
import { Writer } from '../models/writer.model';
import { Genre } from '../models/genre.model';
export interface Author {
  id_user: number;
  username: string;
  profile_picture_url: string | null;
}
export interface SearchResults {
  books: Book[];
  writers: Writer[];
  genres: Genre[];
  authors: Author[];
}
@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient, private router: Router) {}
  searchDatabase(query: string): Observable<SearchResults> {
    const params = new HttpParams().set('query', query);
    return this.http.get<SearchResults>(`${this.apiUrl}/search.php`, { params });
  }
  navigateToSearch(query: string) {
    if (query.trim() !== '') {
      this.router.navigate(['/search'], { queryParams: { query: query } });
    }
  }
}