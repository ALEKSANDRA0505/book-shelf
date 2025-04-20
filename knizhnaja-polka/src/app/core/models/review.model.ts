import { User } from './user.model';
export interface Review {
  id_review: number;
  id_user: number;
  user_username: string;
  profile_picture_url?: string | null;
  id_book: number;
  book_title: string;
  rating: number;
  review_text: string | null;
  created_at: string;
  id_genre?: number | null;
  user?: Partial<User>;
  book_cover_url?: string | null;
}
