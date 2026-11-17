import {
  API_BASE_URL,
  ensureSuccessfulResponse,
  fetchWithRetry,
} from "./client";

export interface GlossaryTerm {
  id: string;
  term: string;
  definition: string;
  category: string;
}

export async function getGlossaryTerms(): Promise<GlossaryTerm[]> {
  const response = await fetchWithRetry(`${API_BASE_URL}/glossary`, {
    method: "GET",
    headers: { Accept: "application/json" },
  });

  await ensureSuccessfulResponse(response);
  return response.json();
}
