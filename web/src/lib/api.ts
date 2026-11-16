import type {
  AnswerResponse,
  EssayQuestionResponse,
  EssaySubmissionResponse,
  PendingEssayResponse,
  ReviewResponse,
} from "@/types";

const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

function getAuthorizationHeader(): string {
  const token = process.env.NEXT_PUBLIC_API_TOKEN;

  if (!token || token === "replace-with-your-sanctum-token") {
    throw new Error(
      "NEXT_PUBLIC_API_TOKEN belum diisi dengan token Sanctum yang valid.",
    );
  }

  return `Bearer ${token}`;
}

export async function fetchWithRetry(
  url: string,
  options: RequestInit,
  retries = 2,
  delayMs = 1500,
): Promise<Response> {
  for (let attempt = 0; ; attempt++) {
    try {
      return await fetch(url, options);
    } catch (error) {
      if (!(error instanceof TypeError) || attempt >= retries) {
        throw error;
      }

      await new Promise((resolve) => setTimeout(resolve, delayMs));
    }
  }
}

async function ensureSuccessfulResponse(response: Response): Promise<void> {
  if (!response.ok) {
    const detail = await response.text();
    throw new Error(
      `Permintaan API gagal (${response.status})${detail ? `: ${detail}` : ""}`,
    );
  }
}

export async function getReviewItems(): Promise<ReviewResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/review`, {
    method: "GET",
    headers: getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAnswer(
  itemId: string,
  correct: boolean,
): Promise<AnswerResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/answer`, {
    method: "POST",
    headers: getHeaders(true),
    body: JSON.stringify({
      item_id: itemId,
      correct,
      request_id: crypto.randomUUID(),
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitEssay(
  itemId: string,
  answerText: string,
  requestId: string,
): Promise<EssaySubmissionResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/submit`, {
    method: "POST",
    headers: getHeaders(true),
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
    headers: getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getPendingEssay(): Promise<PendingEssayResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/essay/review`, {
    method: "GET",
    headers: getHeaders(),
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
    headers: getHeaders(true),
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

function getHeaders(json = false): Headers {
  const headers = new Headers({ Accept: "application/json" });
  headers.set("Authorization", getAuthorizationHeader());

  if (json) {
    headers.set("Content-Type", "application/json");
  }

  return headers;
}
