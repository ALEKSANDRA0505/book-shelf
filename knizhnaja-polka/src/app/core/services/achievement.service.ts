import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { Achievement } from '../models/achievement.model';
@Injectable({
  providedIn: 'root'
})
export class AchievementService {
  private apiUrl = environment.apiUrl;
  constructor(private http: HttpClient) { }
  getAchievements(): Observable<Achievement[]> {
    return this.http.get<Achievement[]>(`${this.apiUrl}/achievement_read.php`);
  }
  getAchievementById(id: number): Observable<Achievement> {
    return this.http.get<Achievement>(`${this.apiUrl}/achievement_read.php?id=${id}`);
  }
  createAchievement(achievementData: { name: string; description?: string | null; icon_url?: string | null }): Observable<{ message: string; id_achievement: number; }> {
    return this.http.post<{ message: string; id_achievement: number; }>(`${this.apiUrl}/achievement_create.php`, achievementData);
  }
  updateAchievement(id: number, achievementData: { name: string; description?: string | null; icon_url?: string | null }): Observable<{ message: string; id_achievement: number; }> {
    const payload = { ...achievementData, id_achievement: id };
    return this.http.put<{ message: string; id_achievement: number; }>(`${this.apiUrl}/achievement_update.php`, payload);
  }
  deleteAchievement(id: number): Observable<{ message: string; id_achievement: number; }> {
    return this.http.delete<{ message: string; id_achievement: number; }>(`${this.apiUrl}/achievement_delete.php?id=${id}`);
  }
} 