
export interface Subscription {
  id_subscription: number; 
  id_user: number;
  username: string;
  profile_picture_url: string | null;
  subscription_date?: string;
} 