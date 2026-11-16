import type {
  ActiveRecallsResponse,
  RecallRequestInput,
  RecallRequestResponse,
  RecallVoteResponse,
} from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function getActiveRecalls(): Promise<ActiveRecallsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/recall/active`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitRecallRequest(
  input: RecallRequestInput,
): Promise<RecallRequestResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/recall/request`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify(input),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitRecallVote(
  recallRequestId: string,
  vote: boolean,
  requestId: string,
): Promise<RecallVoteResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/recall/vote`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      recall_request_id: recallRequestId,
      vote,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
