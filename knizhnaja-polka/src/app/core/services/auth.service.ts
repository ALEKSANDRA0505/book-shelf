import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, of, throwError, finalize } from 'rxjs';
import { tap, catchError, switchMap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';
interface AuthResponse {
  token: string;
  user: User;
  message: string;
}
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private loggedIn = new BehaviorSubject<boolean>(this.hasToken());
  private currentUser = new BehaviorSubject<User | null>(null);
  private isLoading = new BehaviorSubject<boolean>(false);
  constructor(private http: HttpClient) {
    /*
    if (this.isLoggedIn()) {
      this.isLoading.next(true);
      this.loadCurrentUser().subscribe(); 
    }
    */
    
    setTimeout(() => {
      if (this.isLoggedIn()) {
        console.log('AuthService: Token found on init, loading current user (delayed).');
        this.loadCurrentUser().subscribe({
          next: (user) => console.log('AuthService: Delayed initial loadCurrentUser success.', user),
          error: (err) => console.error('AuthService: Delayed initial loadCurrentUser failed.', err)
        });
      }
    }, 0);
  }
  get isLoggedIn$(): Observable<boolean> {
    return this.loggedIn.asObservable();
  }
  get currentUser$(): Observable<User | null> {
    return this.currentUser.asObservable();
  }
  get isLoading$(): Observable<boolean> {
    return this.isLoading.asObservable();
  }
  isLoggedIn(): boolean {
    return this.loggedIn.getValue();
  }
  private saveToken(token: string): void {
    localStorage.setItem('authToken', token);
    this.loggedIn.next(true);
  }
  getToken(): string | null {
    return localStorage.getItem('authToken');
  }
  private removeToken(): void {
    localStorage.removeItem('authToken');
    this.loggedIn.next(false);
    this.currentUser.next(null);
  }
  private hasToken(): boolean {
    return !!localStorage.getItem('authToken');
  }
  /**
   * Вход пользователя
   * @param credentials email и password
   * @returns Observable<User | null> - Возвращает загруженного пользователя или null в случае ошибки
   */
  login(credentials: { email: string; password: string }): Observable<User | null> {
    console.log('AuthService: Logging in...');
    return this.http.post<AuthResponse>(`${this.apiUrl}/login.php`, credentials).pipe(
      switchMap(response => {
        if (response && response.token) {
          this.saveToken(response.token);
          console.log('Login successful, token saved. Loading current user...');
          return this.loadCurrentUser(); 
        } else {
           console.error('Login failed: Invalid response structure from API (missing token)', response);
           this.removeToken();
           return throwError(() => new Error(response?.message || 'Login failed: Invalid API response'));
        }
      }),
      catchError(error => {
        console.error('Login or loadCurrentUser error:', error);
        this.removeToken();
        const errorMessage = error?.error?.error ||
                             error?.message ||
                             'Неизвестная ошибка входа'; 
        return throwError(() => new Error(errorMessage)); 
      })
    );
  }
  /**
   * Регистрация пользователя
   * @param userData Данные пользователя (username, email, password и т.д.)
   * @returns Observable<any> (Зависит от ответа register.php)
   */
  register(userData: Partial<User>): Observable<any> {
    console.log('AuthService: Registering...');
    return this.http.post<any>(`${this.apiUrl}/register.php`, userData).pipe(
       tap((response) => {
         console.log('Registration successful:', response);
       }),
       catchError(error => {
         console.error('Register API error:', error);
         const errorMessage = error?.error?.error ||
                            error?.message ||
                           'Ошибка регистрации';
         return throwError(() => new Error(errorMessage));
       })
    );
  }
  /**
   * Выход пользователя
   */
  logout(): void {
    console.log('AuthService: Logging out...');
    this.removeToken();
  }
  /**
   * Загружает данные текущего пользователя (если есть токен)
   * Управляет состоянием isLoading$
   * @returns Observable<User | null>
   */
  loadCurrentUser(): Observable<User | null> {
     const currentToken = this.getToken();
     if (!currentToken) {
       this.currentUser.next(null);
       this.isLoading.next(false); 
       return of(null);
     }
     this.isLoading.next(true);
     console.log('AuthService: Loading current user data...');
     
     return this.http.get<User>(`${this.apiUrl}/get_current_user.php`).pipe(
       tap(user => {
         if (user) {
            this.currentUser.next(user);
            console.log('Current user data loaded:', user);
         } else {
            console.warn('Received null user data despite having a token.');
            this.removeToken();
         }
       }),
       catchError(error => {
         console.error('Failed to load current user:', error);
         this.removeToken();
         return of(null);
       }),
       finalize(() => {
           this.isLoading.next(false);
           console.log('AuthService: Finished loading current user data.');
       })
     );
  }
}
