import type { UserReputation } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export interface ReputationLeaderboardStats {
  members_count: number;
  mastery_distribution: Array<{
    level: 1 | 2 | 3 | 4;
    members: number;
  }>;
  contributions: {
    lapangan: number;
    non_fisik: number;
    total: number;
  };
}

export async function getReputationLeaderboardStats(): Promise<ReputationLeaderboardStats> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/reputation/leaderboard-stats`,
    {
      method: "GET",
      headers: { Accept: "application/json" },
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getUserReputation(): Promise<UserReputation> {
  const response = await fetchWithRetry(`${API_BASE_URL}/reputation`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getMemberReputation(): Promise<{
  mastery_scores: Record<string, number>;
  overall_mastery_level: number;
  participation_score: number;
  eligible_roles: Record<string, boolean>;
  ledger: {
    data: Array<{
      id: string;
      sequence: number;
      score_type: "mastery" | "participation";
      domain: string | null;
      category: string | null;
      source_type: string;
      source_id: string;
      delta: number;
      reason: string;
      request_id: string;
      previous_hash: string | null;
      hash: string;
      created_at: string;
    }>;
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}> {
  const response = await fetchWithRetry(`${API_BASE_URL}/reputation/me`, {
    method: "GET",
    headers: await getHeaders(),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
