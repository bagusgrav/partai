import { getHeaders, SSO_URL } from "./auth";
import { API_BASE_URL, ensureSuccessfulResponse, fetchWithRetry } from "./client";

export interface FinanceSummary {
  verified_inflow_idr: number;
  pending_audit_inflow_idr: number;
  verified_outflow_idr: number;
  net_cash_balance_idr: number;
  fund_composition: {
    member_dues: number;
    individual_donations: number;
    corporate_donations: number;
  };
  political_education_ratio: number;
  political_education_target_ratio: number;
  political_education_target_met: boolean;
  constitutional_limits: {
    individual_annual_idr: number;
    corporate_annual_idr: number;
    member_annual_idr: number;
  };
  chain_integrity: {
    valid: boolean;
    total_entries: number;
    latest_hash: string | null;
    broken_at_sequence: number | null;
  };
}

export interface FinanceLedgerEntry {
  id: string;
  sequence: number;
  entry_type: "inflow" | "outflow" | "reversal";
  channel: string;
  fiscal_year: number;
  transaction_date: string;
  amount_idr: number;
  donor_type: "member" | "individual" | "corporate" | "internal_treasury";
  contributor_display_name: string;
  masked_identity: string | null;
  purpose_or_program: string;
  province_name: string | null;
  compliance_status: "pending_audit" | "verified" | "rejected_refunded";
  evidence_reference: string | null;
  reversal_of_id: string | null;
  previous_hash: string | null;
  hash: string;
}

export interface MyDues {
  year: number;
  annual_accumulated_idr: number;
  annual_remaining_idr: number;
  annual_limit_idr: number;
  entries: Array<{
    id: string;
    amount_idr: number;
    transaction_date: string;
    evidence_reference: string;
    compliance_status: string;
    purpose_or_program: string;
    hash: string;
  }>;
}

export async function getFinanceSummary(): Promise<FinanceSummary> {
  const response = await fetchWithRetry(`${API_BASE_URL}/finance/summary`, {
    headers: { Accept: "application/json" },
  });
  await ensureSuccessfulResponse(response);
  const payload = await response.json() as { summary: FinanceSummary };
  return payload.summary;
}

export async function getFinanceLedger(params: Record<string, string> = {}) {
  const query = new URLSearchParams(params);
  const response = await fetchWithRetry(
    `${API_BASE_URL}/finance/ledger${query.size ? `?${query}` : ""}`,
    { headers: { Accept: "application/json" } },
  );
  await ensureSuccessfulResponse(response);
  return response.json() as Promise<{ ledger: { data: FinanceLedgerEntry[]; current_page: number; last_page: number } }>;
}

export async function submitDonation(input: {
  donor_type: "individual" | "corporate";
  contributor_name: string;
  identity_number: string;
  amount_idr: number;
  transaction_date: string;
  purpose_or_program?: string;
  evidence_reference: string;
  legal_declaration_accepted: true;
}) {
  const response = await fetchWithRetry(`${API_BASE_URL}/finance/donations`, {
    method: "POST",
    headers: { Accept: "application/json", "Content-Type": "application/json" },
    body: JSON.stringify({
      ...input,
      request_id: crypto.randomUUID(),
    }),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getMyDues(): Promise<MyDues> {
  const response = await fetchWithRetry(`${API_BASE_URL}/finance/my-dues`, {
    headers: await getHeaders(),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitMyDues(input: {
  amount_idr: number;
  transaction_date: string;
  evidence_reference: string;
  legal_declaration_accepted: true;
}) {
  const response = await fetchWithRetry(`${API_BASE_URL}/finance/my-dues`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      ...input,
      request_id: crypto.randomUUID(),
    }),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export function redirectToSso() {
  window.location.assign(SSO_URL);
}
