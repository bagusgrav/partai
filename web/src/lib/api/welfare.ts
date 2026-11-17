import type { WelfareRegionsResponse, WelfareSummaryResponse } from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export type VulnerabilityTier = "rendah" | "sedang" | "tinggi" | "kritis";

export interface WelfareIkkRegion {
  id: string;
  region_code: string;
  region_name: string;
  province_name: string;
  period_year: number;
  poverty_rate: number;
  gini_ratio: number;
  open_unemployment_rate: number;
  stunting_rate: number;
  disaster_events_count: number;
  affected_population: number;
  disaster_risk_score: number;
  ikk_score: number;
  vulnerability_tier: VulnerabilityTier;
  bps_source_citation: string;
  bnpb_source_citation: string;
  last_synced_at: string | null;
}

export interface WelfareIkkMethodologyComponent {
  label: string;
  weight: number;
  min: number;
  max: number;
  unit: string;
}

export interface WelfareIkkSummary {
  year: number;
  average_ikk: number | null;
  distribution: Record<VulnerabilityTier, number>;
  methodology: {
    poverty_rate: WelfareIkkMethodologyComponent;
    stunting_rate: WelfareIkkMethodologyComponent;
    gini_ratio: WelfareIkkMethodologyComponent;
    open_unemployment_rate: WelfareIkkMethodologyComponent;
    disaster_risk_score: WelfareIkkMethodologyComponent;
  };
}

export interface WelfareIkkResponse {
  summary: WelfareIkkSummary;
  provinces: string[];
  regions: {
    data: WelfareIkkRegion[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface WelfareIkkFilters {
  province?: string;
  tier?: VulnerabilityTier;
  year?: number;
  search?: string;
  sort?: "ikk_desc" | "ikk_asc" | "poverty_desc" | "disaster_desc";
  page?: number;
}

export interface WelfareIkkDetail {
  region: WelfareIkkRegion;
  sub_scores: Record<
    | "poverty_rate"
    | "stunting_rate"
    | "gini_ratio"
    | "open_unemployment_rate"
    | "disaster_risk_score",
    number
  >;
  sources: {
    bps: string;
    bnpb: string;
  };
  internal_work: {
    faction_contracts: {
      id: string;
      politician_name: string;
      title: string;
      status: string;
      region_code: string | null;
      province_name: string | null;
    }[];
    events: {
      id: string;
      title: string;
      description: string;
      starts_at: string;
      ends_at: string;
      region_code: string | null;
      province_name: string | null;
    }[];
  };
}

export async function getWelfareIkk(
  filters: WelfareIkkFilters = {},
): Promise<WelfareIkkResponse> {
  const query = new URLSearchParams();
  for (const [key, value] of Object.entries(filters)) {
    if (value !== undefined && value !== "") {
      query.set(key, String(value));
    }
  }
  const suffix = query.size > 0 ? `?${query.toString()}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}/welfare/ikk${suffix}`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getWelfareIkkRegion(
  regionCode: string,
): Promise<WelfareIkkDetail> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/welfare/ikk/${encodeURIComponent(regionCode)}`,
    {
      method: "GET",
      headers: { Accept: "application/json" },
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}

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
