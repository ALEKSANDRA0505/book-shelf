import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HeaderComponent } from './components/header/header.component';
import { SearchBarComponent } from './components/search-bar/search-bar.component';
@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    FormsModule,
    HeaderComponent,
    SearchBarComponent
  ],
  exports: []
})
export class SharedModule { }
