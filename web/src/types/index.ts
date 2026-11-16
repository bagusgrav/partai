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
