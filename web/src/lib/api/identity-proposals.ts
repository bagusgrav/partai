import { getHeaders } from "./auth";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export interface IdentityProposal {
  id: string;
  proposed_name: string;
  proposed_color: string;
  proposed_logo_desc: string;
  created_at: string;
}

export interface IdentityProposalsResponse {
  proposals: IdentityProposal[];
}

export async function getIdentityProposals(): Promise<IdentityProposalsResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/identity-proposals`, {
    headers: { Accept: "application/json" },
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitIdentityProposal(input: {
  proposed_name: string;
  proposed_color: string;
  proposed_logo_desc: string;
}): Promise<{ message: string; proposal: { id: string; status: "pending" } }> {
  const response = await fetchWithRetry(`${API_BASE_URL}/identity-proposals`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify(input),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}
