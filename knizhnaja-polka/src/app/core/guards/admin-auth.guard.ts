import { Injectable } from '@angular/core';
import { CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, UrlTree, Router } from '@angular/router';
import { Observable, map, take, filter, switchMap, of } from 'rxjs';
import { AuthService } from '../services/auth.service';
@Injectable({
  providedIn: 'root'
})
export class AdminAuthGuard implements CanActivate {
  constructor(private authService: AuthService, private router: Router) {}
  canActivate(
    route: ActivatedRouteSnapshot,
    state: RouterStateSnapshot): Observable<boolean | UrlTree> | Promise<boolean | UrlTree> | boolean | UrlTree {
    
    return this.authService.isLoading$.pipe(
      take(1),
      switchMap(isLoading => {
        if (isLoading) {
          console.log('AdminAuthGuard: Initial loading in progress, waiting...');
          return this.authService.isLoading$.pipe(
            filter(loading => !loading),
            take(1),
            switchMap(() => this.checkAdminStatus())
          );
        } else {
          console.log('AdminAuthGuard: Initial loading not in progress, checking status directly.');
          return this.checkAdminStatus();
        }
      })
    );
  }
  private checkAdminStatus(): Observable<boolean | UrlTree> {
    return this.authService.currentUser$.pipe(
      take(1),
      map(user => {
        if (user && user.status === 'Админ') {
          console.log('AdminAuthGuard: Access granted. User is Admin.');
          return true;
        } else {
          console.warn('AdminAuthGuard: Access denied. User status is not \'Админ\' or user is null.');
          return this.router.createUrlTree(['/']);
        }
      })
    );
  }
} 