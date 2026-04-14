
export interface Event {
  id: string;
  title: string;
  date: string;
  location: string;
  image: string;
  category: 'Sosyal' | 'Fotoğraf' | 'Edebiyat' | 'Burs' | 'Spor' | 'Kültür' | 'Söyleşi';
  description: string;
}

export interface NewsItem {
  id: string;
  title: string;
  summary: string;
  date: string;
  image: string;
}

export interface ChatMessage {
  role: 'user' | 'model';
  text: string;
  isError?: boolean;
}

export interface WorkingGroup {
  id: string;
  title: string;
  description: string;
  longDescription?: string;
  icon?: string;
  image: string;
  color?: string;
}

export enum ViewState {
  HOME = 'HOME',
  ABOUT = 'ABOUT',
  EVENTS = 'EVENTS',
  SOLIDARITY = 'SOLIDARITY',
  CONTACT = 'CONTACT',
  MEMBERSHIP_WHY = 'MEMBERSHIP_WHY',
  MEMBERSHIP_UPDATE = 'MEMBERSHIP_UPDATE',
  MEMBERSHIP_DUES = 'MEMBERSHIP_DUES',
  MEMBERSHIP_BENEFITS = 'MEMBERSHIP_BENEFITS',
  MANAGEMENT_BOARD = 'MANAGEMENT_BOARD',
  MANAGEMENT_PAST = 'MANAGEMENT_PAST',
  DOCUMENTS = 'DOCUMENTS'
}
