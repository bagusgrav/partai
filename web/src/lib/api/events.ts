import type { ActiveEventsResponse } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function getActiveEvents(): Promise<ActiveEventsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/events/active`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getEventQrToken(eventId: string): Promise<string> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/events/${encodeURIComponent(eventId)}/qr`,
    {
      method: "GET",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}
