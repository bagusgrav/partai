export interface Item {
  id: string;
  domain: string;
  level: string;
  payload: {
    type?: "multiple-choice";
    question: string;
    options: string[];
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
    correct: boolean;
    correct_option: number;
    explanation: string | null;
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
    explanation: string | null;
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

export interface ActiveRecall {
  id: string;
  target_user_id: string;
  target_name: string;
  reason: string;
  evidence_url: string | null;
  created_at: string;
  status: "voting";
  agree_count: number;
  reject_count: number;
  active_member_count: number;
  votes_required: number;
  has_voted: boolean;
}

export interface ActiveRecallsResponse {
  recalls: ActiveRecall[];
}

export interface RecallRequestInput {
  target_user_id: string;
  reason: string;
  evidence_url: string;
  request_id: string;
}

export interface RecallRequestResponse {
  status: "processed" | "already_processed";
  recall: {
    id: string;
    target_user_id: string;
    reason?: string;
    evidence_url?: string | null;
    status: "pending" | "voting" | "approved" | "rejected";
  };
}

export interface RecallVoteResponse {
  status: "processed" | "already_processed";
  data: {
    vote_id: string;
    recall_status?: "voting" | "approved";
    agree_count?: number;
    votes_required?: number;
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

export interface EconomicIndicator {
  id: number;
  indicator_type: string;
  title: string;
  value: number;
  unit: string;
  period: string;
  source: string;
  trend: string | null;
  updated_at: string;
}

export interface EconomicSummaryResponse {
  indicators: EconomicIndicator[];
  last_updated: string | null;
}

export interface EconomicHistoryResponse {
  type: string;
  data: EconomicIndicator[];
}

export interface GovernmentInsight {
  id: number;
  category: "kabinet" | "berita";
  title: string;
  content: string;
  source_url: string | null;
  image_url: string | null;
  published_at: string | null;
}

export interface GovernmentInsightsResponse {
  insights: GovernmentInsight[];
}

export interface RegisteredUser {
  id: string;
  name: string;
  email: string;
  cluster_id: string | null;
  cluster_name: string | null;
  role: "guest" | "member" | "admin";
  is_verified: boolean;
  reputation_points: number;
  reputation_level: string;
}

export interface ReputationLog {
  id: string;
  action: string;
  points: number;
  description: string;
  created_at: string;
}

export interface UserReputation {
  reputation_points: number;
  reputation_level: string;
  logs: ReputationLog[];
}
