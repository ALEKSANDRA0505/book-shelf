
export interface User {
  id_user: number;
  username: string;
  email: string;
  profile_picture_url?: string | null;
  age?: number | null;
  city?: string | null;
  status?: string | null;
  about_me?: string | null;
  reading_goal?: number | null;
  read_books_count?: number;
  created_at: string;
  
  password?: string;
}
