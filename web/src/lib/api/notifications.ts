import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export const NOTIFICATIONS_UPDATED_EVENT = "partai:notifications-updated";

export interface DailyDigestData {
  type: string;
  title: string;
  quiz: {
    id: string;
    question: string;
    domain: string;
    level: string;
  };
  news: {
    title: string;
    content: string;
    source_url: string | null;
    published_at: string | null;
  };
  welfare: {
    region_name: string;
    indicator: string;
    value: number;
    unit: string;
    year: number;
    source: string;
  };
}

export interface AppNotification {
  id: string;
  type: string;
  data: DailyDigestData;
  read_at: string | null;
  created_at: string;
}

export interface NotificationsResponse {
  notifications: AppNotification[];
  unread_count: number;
}

export async function getNotifications(): Promise<NotificationsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/notifications`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function markAsRead(id: string): Promise<void> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/notifications/${encodeURIComponent(id)}/read`,
    {
      method: "POST",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);

  if (typeof window !== "undefined") {
    window.dispatchEvent(new Event(NOTIFICATIONS_UPDATED_EVENT));
  }
}
