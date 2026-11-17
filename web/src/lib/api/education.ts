import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export const CIVIC_DOMAINS = [
  "hak_konstitusional",
  "alasan_bernegara",
  "keuangan_negara",
  "hukum",
  "politik_bernegara",
] as const;

export type CivicDomain = (typeof CIVIC_DOMAINS)[number];
export type MasteryLevel = 1 | 2 | 3 | 4;
export type AnswerOptionKey = "A" | "B" | "C" | "D";

export interface CivicQuestion {
  id: string;
  domain: CivicDomain;
  mastery_level: MasteryLevel;
  prompt: string;
  options: Array<{ key: AnswerOptionKey; text: string }>;
  source_citation: string;
  source_url: string | null;
  is_due?: boolean;
  next_review_at?: string | null;
}

export interface EducationOverview {
  curriculum: Array<{
    domain: CivicDomain;
    label: string;
    levels: Record<MasteryLevel, string>;
  }>;
  distribution: Record<
    CivicDomain,
    { count: number; levels: Record<MasteryLevel, number> }
  >;
  daily_digest: {
    date: string;
    featured_question: CivicQuestion | null;
    context: {
      active_faction_contracts: number;
      upcoming_civic_agendas: Array<{
        id: string;
        title: string;
        starts_at: string;
      }>;
    };
  };
  preview_questions: CivicQuestion[];
}

export interface EducationSession {
  questions: CivicQuestion[];
  mastery_scores: Record<CivicDomain, number>;
  overall_mastery_level: number;
}

export interface EducationAnswerResponse {
  correct: boolean;
  correct_option: AnswerOptionKey;
  explanation: string;
  source_citation: string;
  source_url: string | null;
  mastery_awarded: boolean;
  points_delta: number;
  next_review_at: string | null;
  updated_reputation: {
    mastery_scores: Record<CivicDomain, number>;
    overall_mastery_level: number;
  } | null;
}

export async function getEducationOverview(): Promise<EducationOverview> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/education/overview`,
    {
      method: "GET",
      headers: { Accept: "application/json" },
    },
  );
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getEducationSession(): Promise<EducationSession> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/education/session`,
    {
      method: "GET",
      headers: await getHeaders(),
    },
  );
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitEducationAnswer(
  questionId: string,
  selectedOption: AnswerOptionKey,
  confidence: "ragu" | "yakin",
  requestId = crypto.randomUUID(),
): Promise<EducationAnswerResponse> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/education/answers`,
    {
      method: "POST",
      headers: await getHeaders(true),
      body: JSON.stringify({
        request_id: requestId,
        question_id: questionId,
        selected_option: selectedOption,
        confidence,
      }),
    },
  );
  await ensureSuccessfulResponse(response);
  return response.json();
}
