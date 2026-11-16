import type { WelfareRegionsResponse, WelfareSummaryResponse } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export async function getWelfareSummary(): Promise<WelfareSummaryResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/welfare/summary`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getWelfareRegions(): Promise<WelfareRegionsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/welfare/regions`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
