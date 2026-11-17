import EconomicDashboard from "@/components/features/economics/EconomicDashboard";
import WelfareIndexDashboard from "@/components/features/welfare/WelfareIndexDashboard";
import PublicQuestionButton from "@/components/PublicQuestionButton";

export default function InfoPage() {
  return (
    <>
      <WelfareIndexDashboard />
      <EconomicDashboard />
      <PublicQuestionButton />
    </>
  );
}
