import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
export const loginGuard: CanActivateFn = (route, state) => {
  
  const authService = inject(AuthService);
  const router = inject(Router);
  if (authService.isLoggedIn()) {
    console.log('LoginGuard: User already logged in, redirecting to home.');
    router.navigate(['/']); 
    return false;
  } else {
    return true; 
  }
}; 