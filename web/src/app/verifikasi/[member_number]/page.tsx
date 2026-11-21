import MemberVerificationPage from "@/components/features/profile/MemberVerificationPage";

interface VerificationRouteProps {
  params: Promise<{ member_number: string }>;
}

export default async function VerificationRoute({
  params,
}: VerificationRouteProps) {
  const { member_number: memberNumber } = await params;

  return <MemberVerificationPage memberNumber={memberNumber} />;
}
