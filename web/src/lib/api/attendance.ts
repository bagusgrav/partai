import type {
  AttendanceAttestationResult,
  AttendanceClaimResult,
  PendingAttendanceClaimsResponse,
} from "@/types";
import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";
import { getHeaders } from "./auth";

export async function submitAttendanceClaim(
  eventId: string,
  token: string,
  lat: number,
  lng: number,
  requestId: string,
): Promise<AttendanceClaimResult> {
  const response = await fetchWithRetry(`${API_BASE_URL}/attendance/claim`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      event_id: eventId,
      token,
      lat,
      lng,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function getPendingAttendanceClaims(
  eventId: string,
): Promise<PendingAttendanceClaimsResponse> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/attendance/pending?event_id=${encodeURIComponent(eventId)}`,
    {
      method: "GET",
      headers: await getHeaders(),
    },
  );

  await ensureSuccessfulResponse(response);
  return response.json();
}

export async function submitAttendanceAttestation(
  claimId: string,
  requestId: string,
): Promise<AttendanceAttestationResult> {
  const response = await fetchWithRetry(`${API_BASE_URL}/attendance/attest`, {
    method: "POST",
    headers: await getHeaders(true),
    body: JSON.stringify({
      claim_id: claimId,
      request_id: requestId,
    }),
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
