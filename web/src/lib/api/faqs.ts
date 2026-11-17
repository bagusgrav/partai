import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export interface Faq {
  id: string;
  question: string;
  answer: string;
}

export interface InquirySubmission {
  request_id: string;
  name?: string;
  email?: string;
  question: string;
}

export async function getFaqs(): Promise<Faq[]> {
  const response = await fetchWithRetry(`${API_BASE_URL}/faqs`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitInquiry(data: InquirySubmission): Promise<void> {
  const response = await fetchWithRetry(`${API_BASE_URL}/inquiries`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  await ensureSuccessfulResponse(response);
}
