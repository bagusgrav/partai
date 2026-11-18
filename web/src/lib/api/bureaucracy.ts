import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export type PerformanceTier =
  | "sangat_baik"
  | "baik"
  | "perlu_perbaikan"
  | "kritis";

export interface PublicAgencyScorecard {
  id: string;
  agency_code: string;
  agency_name: string;
  agency_category: "kementerian_lembaga" | "pemerintah_provinsi" | "pemerintah_kab_kota" | "pelayanan_publik";
  province_name: string | null;
  region_code: string | null;
  period_year: number;
  rb_index_score: number;
  sakip_grade: string;
  merit_system_score: number;
  ombudsman_compliance_score: number;
  resolved_complaints_count: number;
  open_complaints_count: number;
  bureaucracy_integrity_score: number;
  performance_tier: PerformanceTier;
  source_citation: string;
}

export interface ScorecardResponse {
  summary: {
    year: number;
    average_integrity_score: number | null;
    distribution: Record<PerformanceTier, number>;
    verified_reports_count: number;
    forwarded_to_ombudsman_count: number;
    resolved_reports_count: number;
  };
  scorecards: {
    data: PublicAgencyScorecard[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface BureaucracyReportPublic {
  id: string;
  ticket_code: string;
  agency_name: string;
  province_name: string | null;
  category: string;
  title: string;
  chronology: string;
  incident_date: string;
  asset_identifier: string | null;
  evidence_url: string | null;
  verification_status: "verified" | "forwarded_to_ombudsman" | "resolved";
  public_resolution_note: string | null;
  verified_at: string | null;
  created_at: string;
}

export interface PublicReportsResponse {
  reports: {
    data: BureaucracyReportPublic[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface CreateBureaucracyReportPayload {
  agency_id?: string | null;
  agency_name: string;
  province_name?: string | null;
  region_code?: string | null;
  category: "maladministrasi" | "penyalahgunaan_fasilitas" | "pungli_layanan" | "penundaan_berlarut" | "diskriminasi_layanan";
  title: string;
  chronology: string;
  incident_date: string;
  asset_identifier?: string | null;
  evidence_url?: string | null;
}

export interface CreateReportResult {
  ticket_code: string;
  reporter_secret?: string;
  verification_status: string;
  created_at: string;
  status?: "already_processed";
}

export interface TrackReportResult {
  ticket_code: string;
  verification_status: string;
  category: string;
  agency_name: string;
  province_name: string | null;
  chronology?: string;
  public_resolution_note: string | null;
  verified_at: string | null;
}

export async function getBureaucracyScorecards(
  filters: Record<string, string | number | undefined> = {},
): Promise<ScorecardResponse> {
  const query = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== undefined && value !== "") query.set(key, String(value));
  });
  const suffix = query.size > 0 ? `?${query.toString()}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}/bureaucracy/scorecards${suffix}`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getBureaucracyReports(
  filters: Record<string, string | undefined> = {},
): Promise<PublicReportsResponse> {
  const query = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value) query.set(key, value);
  });
  const suffix = query.size > 0 ? `?${query.toString()}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}/bureaucracy/reports${suffix}`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function createBureaucracyReport(
  payload: CreateBureaucracyReportPayload,
): Promise<CreateReportResult> {
  const response = await fetchWithRetry(`${API_BASE_URL}/bureaucracy/reports`, {
    method: "POST",
    headers: { Accept: "application/json", "Content-Type": "application/json" },
    body: JSON.stringify({
      request_id: crypto.randomUUID(),
      ...payload,
    }),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function trackBureaucracyReport(
  ticketCode: string,
  secret?: string,
): Promise<TrackReportResult> {
  const query = secret ? `?secret=${encodeURIComponent(secret)}` : "";
  const response = await fetchWithRetry(
    `${API_BASE_URL}/bureaucracy/reports/track/${encodeURIComponent(ticketCode)}${query}`,
    {
      method: "GET",
      headers: { Accept: "application/json", "Cache-Control": "no-store" },
    },
  );
  await ensureSuccessfulResponse(response);
  return response.json();
}
