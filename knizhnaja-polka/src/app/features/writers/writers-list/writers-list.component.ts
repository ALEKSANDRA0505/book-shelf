import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { Writer } from '../../../core/models/writer.model';
import { WriterService } from '../../../core/services/writer.service';
@Component({
  selector: 'app-writers-list',
  templateUrl: './writers-list.component.html',
  styleUrls: ['./writers-list.component.css'],
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule]
})
export class WritersListComponent implements OnInit {
  searchTerm: string = '';
  allWriters: Writer[] = [];
  filteredWriters: Writer[] = [];
  isLoading: boolean = true;
  error: string | null = null;
  constructor(private router: Router, private writerService: WriterService) { }
  ngOnInit(): void {
    this.loadWriters();
  }
  loadWriters(): void {
    console.log("Загрузка списка писателей из API...");
    this.isLoading = true;
    this.error = null;
    this.writerService.getWriters().subscribe({
      next: (data) => {
        this.allWriters = data;
        this.filteredWriters = [...this.allWriters];
        this.isLoading = false;
        console.log('Writers loaded:', data);
      },
      error: (err) => {
        console.error('Error loading writers:', err);
        this.error = 'Не удалось загрузить список писателей.';
        this.isLoading = false;
      }
    });
  }
  search(): void {
    const term = this.searchTerm.toLowerCase().trim();
    if (!term) {
      this.filteredWriters = [...this.allWriters];
    } else {
      this.filteredWriters = this.allWriters.filter(writer =>
        writer.name.toLowerCase().includes(term)
      );
    }
  }
  goToProfile(): void {
    this.router.navigate(['/profil']);
  }
}