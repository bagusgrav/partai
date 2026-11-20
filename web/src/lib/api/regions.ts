import { API_BASE_URL, ensureSuccessfulResponse, fetchWithRetry } from "./client";

export interface AdministrativeRegion {
  id: string;
  parent_id: string | null;
  name: string;
  level: 1 | 2;
}

interface RegionsResponse {
  regions: AdministrativeRegion[];
}

export async function getRegions(parentId?: string): Promise<RegionsResponse> {
  const query = parentId ? `?parent_id=${encodeURIComponent(parentId)}` : "";
  const response = await fetchWithRetry(`${API_BASE_URL}/regions${query}`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
