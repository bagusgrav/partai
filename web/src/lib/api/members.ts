import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export interface VerifiedMember {
  name: string;
  member_number: string;
  region_name: string | null;
  status: "verified";
}

export async function verifyMember(
  memberNumber: string,
): Promise<VerifiedMember | null> {
  const response = await fetchWithRetry(
    `${API_BASE_URL}/members/verify/${encodeURIComponent(memberNumber)}`,
    { headers: { Accept: "application/json" } },
  );

  if (response.status === 404) {
    return null;
  }

  await ensureSuccessfulResponse(response);

  const payload: unknown = await response.json();
  if (
    typeof payload !== "object" ||
    payload === null ||
    !("name" in payload) ||
    typeof payload.name !== "string" ||
    !("member_number" in payload) ||
    typeof payload.member_number !== "string" ||
    !("status" in payload) ||
    payload.status !== "verified" ||
    !("region_name" in payload) ||
    (typeof payload.region_name !== "string" && payload.region_name !== null)
  ) {
    throw new Error("Respons verifikasi anggota tidak valid.");
  }

  return {
    name: payload.name,
    member_number: payload.member_number,
    region_name: payload.region_name,
    status: payload.status,
  };
}
