import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { CommonModule } from '@angular/common';
@Component({
  selector: 'app-sign-in',
  templateUrl: './sign-in.component.html',
  styleUrls: ['./sign-in.component.css'],
  standalone: true,
  imports: [
    FormsModule,
    CommonModule
  ]
})
export class SignInComponent {
  loginEmail: string = '';
  loginPassword: string = '';
  registerUsername: string = '';
  registerEmail: string = '';
  registerPassword: string = '';
  confirmPassword: string = '';
  loading = false;
  loginError: string | null = null;
  registerError: string | null = null;
  registerSuccess: string | null = null;
  constructor(
    private router: Router,
    private authService: AuthService,
    private route: ActivatedRoute
  ) {}
  toggleForm() {
    const overlay = document.getElementById('overlay');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const overlayTitle = document.getElementById('overlay-title');
    const overlayText = document.getElementById('overlay-text');
    const switchButton = document.getElementById('switch-button');
    this.loginError = null;
    this.registerError = null;
    this.registerSuccess = null;
    if (overlay?.classList.contains('right')) {
      overlay.classList.remove('right');
      loginForm?.style.setProperty('transform', 'translateX(0)');
      registerForm?.style.setProperty('transform', 'translateX(-100%)');
      overlayTitle!.textContent = 'ЧИТАТЕЛЬСКИЙ БИЛЕТ';
      overlayText!.textContent = 'У вас нет билета?';
      switchButton!.textContent = 'Зарегистрируйтесь!';
    } else {
      overlay?.classList.add('right');
      loginForm?.style.setProperty('transform', 'translateX(100%)');
      registerForm?.style.setProperty('transform', 'translateX(0)');
      overlayTitle!.textContent = 'ЧИТАТЕЛЬСКИЙ БИЛЕТ';
      overlayText!.textContent = 'У вас уже есть билет? Войдите!';
      switchButton!.textContent = 'Войти';
    }
  }
  togglePasswordVisibility(inputId: string) {
    const input = document.getElementById(inputId) as HTMLInputElement;
    if (input.type === 'password') {
      input.type = 'text';
    } else {
      input.type = 'password';
    }
  }
  onLogin() {
    this.loading = true;
    this.loginError = null;
    const credentials = { email: this.loginEmail, password: this.loginPassword };
    if (!credentials.email || !credentials.password) {
      this.loginError = 'Email и пароль обязательны.';
      this.loading = false;
      return;
    }
    this.authService.login(credentials).subscribe({
      next: (response) => {
        this.loading = false;
        console.log('Login successful', response);
        const returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/profil';
        console.log('Return URL found:', returnUrl);
        this.router.navigateByUrl(returnUrl);
      },
      error: (err) => {
        this.loading = false;
        this.loginError = err.message || 'Ошибка входа. Проверьте email и пароль.';
        console.error('Login error:', err);
      }
    });
  }
  onRegister() {
    this.loading = true;
    this.registerError = null;
    this.registerSuccess = null;
    if (this.registerPassword !== this.confirmPassword) {
      this.registerError = 'Пароли не совпадают!';
      this.loading = false;
      return;
    }
    if (!this.registerUsername || !this.registerEmail || !this.registerPassword) {
        this.registerError = 'Все поля обязательны для регистрации.';
        this.loading = false;
        return;
    }
    
    if (this.registerPassword.length < 6) {
        this.registerError = 'Пароль должен быть не менее 6 символов.';
        this.loading = false;
        return;
    }
    const userData = {
      username: this.registerUsername,
      email: this.registerEmail,
      password: this.registerPassword
    };
    this.authService.register(userData).subscribe({
      next: (response) => {
        this.loading = false;
        this.registerSuccess = 'Регистрация успешна! Теперь вы можете войти.';
        console.log('Registration successful', response);
        if (!document.getElementById('overlay')?.classList.contains('right')) {
          this.toggleForm(); 
        }
      },
      error: (err) => {
        this.loading = false;
        this.registerError = err.message || 'Ошибка регистрации. Попробуйте снова.';
        console.error('Registration error:', err);
      }
    });
  }
}