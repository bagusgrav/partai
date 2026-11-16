import type { ActiveVotesResponse, VoteSubmissionResponse } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function getActiveVotes(): Promise<ActiveVotesResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/votes/active`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitVote(
  voteId: number,
  voteOptionId: number,
  requestId: string,
): Promise<VoteSubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/votes/submit`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      vote_id: voteId,
      vote_option_id: voteOptionId,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
