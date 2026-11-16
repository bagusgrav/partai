import type { UserReputation } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function getUserReputation(): Promise<UserReputation> {
  const response = await fetchWithRetry(`${API_BASE_URL}/reputation`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
