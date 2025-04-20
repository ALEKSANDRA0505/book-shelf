import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { Writer } from '../../../core/models/writer.model';
import { Book } from '../../../core/models/book.model';
import { WriterService } from '../../../core/services/writer.service';
@Component({
  selector: 'app-writer-profile',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './writer-profile.component.html',
  styleUrl: './writer-profile.component.css'
})
export class WriterProfileComponent implements OnInit {
  writerId: number | null = null;
  writer: Writer | null = null;
  isLoadingWriter: boolean = true;
  isLoadingBooks: boolean = true;
  error: string | null = null;
  books: Book[] = [];
  constructor(
    private route: ActivatedRoute,
    private writerService: WriterService
  ) {}
  ngOnInit(): void {
    this.route.paramMap.subscribe(params => {
      const id = params.get('id');
      this.writerId = id ? +id : null;
      if (this.writerId) {
        this.loadWriterData(this.writerId);
        this.loadWriterBooks(this.writerId);
      } else {
        console.error('ID писателя не найден в параметрах маршрута');
        this.error = 'Не удалось определить ID писателя.';
        this.isLoadingWriter = false;
        this.isLoadingBooks = false;
      }
    });
  }
  loadWriterData(id: number): void {
    console.log(`Загрузка данных для писателя с ID: ${id}`);
    this.isLoadingWriter = true;
    this.error = null;
    this.writerService.getWriterById(id).subscribe({
        next: (data) => {
            this.writer = data;
            this.isLoadingWriter = false;
            console.log('Writer data loaded:', data);
        },
        error: (err) => {
            console.error('Error loading writer data:', err);
            this.error = `Не удалось загрузить данные писателя (ID: ${id}). ${err.message || ''}`.trim();
            this.isLoadingWriter = false;
        }
    });
  }
  loadWriterBooks(writerId: number): void {
      console.log(`Загрузка книг для писателя с ID: ${writerId} из API...`);
      this.isLoadingBooks = true;
      if (!this.error) {
          this.error = null;
      }
      
      this.writerService.getBooksByWriterId(writerId).subscribe({
          next: (data) => {
              this.books = data;
              this.isLoadingBooks = false;
              console.log('Books loaded from API for writer', writerId, data);
          },
          error: (err) => {
              console.error('Error loading books for writer:', err);
              const bookErrorMsg = `Не удалось загрузить книги писателя (ID: ${writerId}). ${err.message || ''}`.trim();
              this.error = this.error ? `${this.error} ${bookErrorMsg}` : bookErrorMsg;
              this.isLoadingBooks = false;
          }
      });
  }
}