import type {
  EssayQuestionResponse,
  EssaySubmissionResponse,
  PendingEssayResponse,
} from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function submitEssay(
  itemId: string,
  answerText: string,
  requestId: string,
): Promise<EssaySubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/submit`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      item_id: itemId,
      answer_text: answerText,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getEssayQuestion(): Promise<EssayQuestionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/question`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getPendingEssay(): Promise<PendingEssayResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/review`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitEssayReview(
  submissionId: number,
  score: number,
  feedback: string | null,
  requestId: string,
): Promise<EssaySubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/review`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      submission_id: submissionId,
      score,
      feedback,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
