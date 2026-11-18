import { API_BASE_URL, ensureSuccessfulResponse, fetchWithRetry } from "./client";
import { getHeaders } from "./auth";

export interface OrganizationSeat {
  id: string;
  seat_code: string;
  parent_id: string | null;
  layer: string;
  title: string;
  territory_level: string;
  region_code: string | null;
  province_name: string | null;
  answering_scope: string;
  selection_method: string;
  required_mastery_level: number;
  required_domain: string | null;
  required_min_participation: number;
  term_duration_months: number;
  max_consecutive_terms: number;
  holder_user_id: string | null;
  holder_display_name: string | null;
  term_starts_at: string | null;
  term_ends_at: string | null;
  status: string;
  sort_order: number;
  children?: OrganizationSeat[];
}

export interface OrganizationTreeResponse {
  tree: OrganizationSeat[];
  seats: OrganizationSeat[];
  summary: {
    total_seats: number;
    filled_seats: number;
    open_seats: number;
  };
  holders: Array<{
    seat_code: string;
    holder_display_name: string | null;
    term_starts_at: string | null;
    term_ends_at: string | null;
  }>;
  responsibility_matrix: Array<{
    layer: string;
    title: string;
    answers: string;
  }>;
}

export interface SeatEligibility {
  id: string;
  seat_code: string;
  title: string;
  status: string;
  required_mastery_level: number;
  required_domain: string | null;
  required_min_participation: number;
  eligible: boolean;
  reasons: string[];
  current_mastery_level: number;
  current_participation_score: number;
}

export interface SeatEligibilityResponse {
  seats: SeatEligibility[];
}

export async function getOrganizationTree(): Promise<OrganizationTreeResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/organization/tree`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getMySeatEligibility(): Promise<SeatEligibilityResponse> {
  const response = await fetchWithRetry(`${API_BASE_URL}/organization/eligibility`, {
    method: "GET",
    headers: await getHeaders(),
  });
  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function applyForOrganizationSeat(
  seatId: string,
  statement: string,
  requestId: string,
): Promise<{ status: string; application: unknown }> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/organization/seats/${seatId}/apply`,
    {
      method: "POST",
      headers: await getHeaders(true),
      body: JSON.stringify({ request_id: requestId, statement }),
    },
  );
  await ensureSuccessfulResponse(response);
  return response.json();
}
