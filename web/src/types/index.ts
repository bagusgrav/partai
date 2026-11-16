export interface Item {
  id: string;
  domain: string;
  level: string;
  payload: {
    type?: "multiple-choice";
    question: string;
    options: string[];
    answer: number;
    explanation: string;
    sources: {
      citation: string;
      url?: string;
    }[];
  } | {
    type: "essay";
    question: string;
    instructions?: string;
  };
}

export interface ReviewResponse {
  items: Item[];
}

export interface AnswerResponse {
  status: "processed" | "already_processed";
  data: {
    next_review_at?: string;
    state?: Record<string, unknown>;
  };
}

export interface PendingEssaySubmission {
  id: number;
  answer_text: string;
  created_at: string;
  item: {
    id: string;
    payload: {
      question: string;
      instructions?: string;
    };
  };
}

export interface PendingEssayResponse {
  submission: PendingEssaySubmission | null;
}

export interface EssayQuestionResponse {
  item: Item | null;
}

export interface EssaySubmissionResponse {
  status: "processed" | "already_processed";
  data: {
    submission_id: number;
  };
}

export interface ActiveEvent {
  id: string;
  title: string;
  description: string;
  location_lat: string;
  location_lng: string;
  radius_m: number;
  starts_at: string;
  ends_at: string;
  coordinator_id: string;
}

export interface ActiveEventsResponse {
  events: ActiveEvent[];
}

export interface VoteOption {
  id: number;
  option_text: string;
}

export interface ActiveVote {
  id: number;
  title: string;
  description: string;
  starts_at: string;
  ends_at: string;
  vote_options: VoteOption[];
}

export interface ActiveVotesResponse {
  votes: ActiveVote[];
}

export interface VoteSubmissionResponse {
  status: "processed" | "already_processed";
  data: {
    submission_id: number;
  };
}

export interface AttendanceClaimResult {
  status: "processed" | "already_processed" | "rejected";
  claim_id?: string;
  claim_status?: "pending" | "valid" | "rejected";
  distance_m: number;
}

export interface PendingAttendanceClaim {
  id: string;
  event_id: string;
  distance_m: number;
  created_at: string;
  member_name: string;
}

export interface PendingAttendanceClaimsResponse {
  claims: PendingAttendanceClaim[];
}

export interface AttendanceAttestationResult {
  status: "processed" | "already_processed";
  claim_status: "pending" | "valid";
  attestations?: number;
}

export interface WelfareSummary {
  average_poverty: number | null;
  poverty_year: number | null;
  average_stunting: number | null;
  stunting_year: number | null;
  disaster_events: number;
  disaster_year: number;
}

export interface WelfareSummaryResponse {
  summary: WelfareSummary;
}

export interface WelfareRegion {
  region_code: string;
  region_name: string;
  poverty: number | null;
  poverty_year: number | null;
  stunting: number | null;
  stunting_year: number | null;
  disaster_events: number;
  disaster_year: number | null;
}

export interface WelfareRegionsResponse {
  regions: WelfareRegion[];
}
