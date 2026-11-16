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
