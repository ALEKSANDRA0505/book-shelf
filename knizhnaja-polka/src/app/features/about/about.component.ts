import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AboutComponent as AboutInnerComponent } from './about/about.component';
@Component({
  selector: 'app-about',
  standalone: true,
  imports: [CommonModule, AboutInnerComponent],
  template: '<app-about></app-about>'
})
export class AboutComponent {} 