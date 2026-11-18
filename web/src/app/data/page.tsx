import WelfareDashboard from "@/components/features/welfare/WelfareDashboard";
import PublicQuestionButton from "@/components/PublicQuestionButton";
import FinanceTransparency from "@/components/features/finance/FinanceTransparency";

export default function DataPage() {
  return (
    <>
      <WelfareDashboard />
      <FinanceTransparency />
      <PublicQuestionButton />
    </>
  );
}
