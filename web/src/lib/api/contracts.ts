import { API_BASE_URL, ensureSuccessfulResponse, fetchWithRetry } from "./client";

export const factionContractStatuses = [
  "BELUM_DIMULAI",
  "SEDANG_BERJALAN",
  "TERPENUHI",
  "GAGAL",
] as const;

export type FactionContractStatus = (typeof factionContractStatuses)[number];

export type FactionContract = {
  id: string;
  politician_id: string | null;
  politician_name: string;
  title: string;
  description: string;
  status: FactionContractStatus;
  evidence_url: string | null;
  deadline: string | null;
  created_at: string;
  updated_at: string;
};

export async function getFactionContracts(
  status?: FactionContractStatus,
): Promise<FactionContract[]> {
  const query = status ? `?status=${encodeURIComponent(status)}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}/contracts${query}`, {
    method: "GET",
    headers: { Accept: "application/json" },
    cache: "no-store",
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
