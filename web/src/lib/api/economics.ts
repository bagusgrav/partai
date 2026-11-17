import type {
  EconomicHistoryResponse,
  EconomicIndicatorsResponse,
  EconomicSummaryResponse,
  GovernmentInsightsResponse,
} from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

async function getJson<T>(path: string): Promise<T> {
  const response = await fetchWithRetry(`${API_BASE_URL}${path}`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export function getEconomicSummary(): Promise<EconomicSummaryResponse> {
  return getJson("/economic/summary");
}

export function getEconomicIndicators(): Promise<EconomicIndicatorsResponse> {
  return getJson("/economy/indicators");
}

export function getEconomicHistory(
  type: string,
): Promise<EconomicHistoryResponse> {
  return getJson(`/economic/history/${encodeURIComponent(type)}`);
}

export function getGovernmentNews(): Promise<GovernmentInsightsResponse> {
  return getJson("/insights/news");
}

export function getGovernmentCabinet(): Promise<GovernmentInsightsResponse> {
  return getJson("/insights/cabinet");
}
