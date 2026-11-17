import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

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

export type FactionContractInput = Pick<
  FactionContract,
  "politician_name" | "title" | "description"
> &
  Partial<
    Pick<
      FactionContract,
      "politician_id" | "status" | "evidence_url" | "deadline"
    >
  >;

export type FactionContractMutationResponse =
  | FactionContract
  | { status: "already_processed"; data: FactionContract };

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

export async function createFactionContract(
  input: FactionContractInput,
): Promise<FactionContractMutationResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/contracts`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({ ...input, request_id: crypto.randomUUID() }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function updateFactionContract(
  id: string,
  input: Partial<FactionContractInput>,
): Promise<FactionContractMutationResponse> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/contracts/${encodeURIComponent(id)}`,
    {
      method: "PATCH",
      headers: await getHeaders(true),
      body: JSON.stringify({ ...input, request_id: crypto.randomUUID() }),
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}
