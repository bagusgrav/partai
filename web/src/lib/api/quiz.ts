import type { AnswerResponse, ReviewResponse } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function getReviewItems(): Promise<ReviewResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/review`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAnswer(
  itemId: string,
  selectedOption: number,
  requestId: string,
): Promise<AnswerResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/answer`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      item_id: itemId,
      selected_option: selectedOption,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
