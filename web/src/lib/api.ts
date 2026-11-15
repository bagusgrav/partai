import type { AnswerResponse, ReviewResponse } from "@/types";

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
  const headers = new Headers({ Accept: "application/json" });
  headers.set("Authorization", getAuthorizationHeader());

  const response = await fetchWithRetry(`${API_BASE_URL}/review`, {
    method: "GET",
    headers,
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAnswer(
  itemId: string,
  correct: boolean,
): Promise<AnswerResponse> {
  const headers = new Headers({
    Accept: "application/json",
    "Content-Type": "application/json",
  });
  headers.set("Authorization", getAuthorizationHeader());

  const response = await fetchWithRetry(`${API_BASE_URL}/answer`, {
    method: "POST",
    headers,
    body: JSON.stringify({
      item_id: itemId,
      correct,
      request_id: crypto.randomUUID(),
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
