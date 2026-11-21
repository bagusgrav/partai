"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import { API_BASE_URL, ensureSuccessfulResponse } from "@/lib/api/client";

export interface PartySettings {
  partyName: string;
  logoPath: string | null;
  primaryColor: string;
  secondaryColor: string;
  tagline: string;
}

interface PartySettingsContextValue {
  settings: PartySettings;
  isLoading: boolean;
}

export const DEFAULT_PARTY_SETTINGS: PartySettings = {
  partyName: "PARTAI",
  logoPath: null,
  primaryColor: "#0f172a",
  secondaryColor: "#ffffff",
  tagline: "Kartu Tanda Anggota",
};

const PartySettingsContext = createContext<PartySettingsContextValue>({
  settings: DEFAULT_PARTY_SETTINGS,
  isLoading: true,
});

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

function parseSettings(payload: unknown): PartySettings {
  if (!isRecord(payload)) {
    throw new Error("Respons branding partai tidak valid.");
  }

  const partyName =
    typeof payload.party_name === "string" && payload.party_name.trim()
      ? payload.party_name
      : DEFAULT_PARTY_SETTINGS.partyName;
  const logoPath =
    typeof payload.logo_path === "string" && payload.logo_path.length > 0
      ? new URL(payload.logo_path, API_BASE_URL).toString()
      : null;
  const primaryColor =
    typeof payload.primary_color === "string" &&
    /^#[0-9a-f]{6}$/i.test(payload.primary_color)
      ? payload.primary_color
      : DEFAULT_PARTY_SETTINGS.primaryColor;
  const secondaryColor =
    typeof payload.secondary_color === "string" &&
    /^#[0-9a-f]{6}$/i.test(payload.secondary_color)
      ? payload.secondary_color
      : DEFAULT_PARTY_SETTINGS.secondaryColor;
  const tagline =
    typeof payload.tagline === "string" && payload.tagline.trim()
      ? payload.tagline
      : DEFAULT_PARTY_SETTINGS.tagline;

  return { partyName, logoPath, primaryColor, secondaryColor, tagline };
}

export function PartySettingsProvider({ children }: { children: ReactNode }) {
  const [settings, setSettings] = useState(DEFAULT_PARTY_SETTINGS);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    let active = true;

    fetch(`${API_BASE_URL}/settings`, {
      headers: { Accept: "application/json" },
    })
      .then(async (response) => {
        await ensureSuccessfulResponse(response);
        return response.json();
      })
      .then(parseSettings)
      .then((nextSettings) => {
        if (active) {
          setSettings(nextSettings);
        }
      })
      .catch((error: unknown) => {
        console.error("Branding partai gagal dimuat; memakai nilai fallback.", error);
      })
      .finally(() => {
        if (active) {
          setIsLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  return (
    <PartySettingsContext.Provider value={{ settings, isLoading }}>
      {children}
    </PartySettingsContext.Provider>
  );
}

export function usePartySettings(): PartySettingsContextValue {
  return useContext(PartySettingsContext);
}
