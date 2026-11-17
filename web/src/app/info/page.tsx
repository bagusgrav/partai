import EconomicDashboard from "@/components/features/economics/EconomicDashboard";
import WelfareIndexDashboard from "@/components/features/welfare/WelfareIndexDashboard";

export default function InfoPage() {
  return (
    <>
      <WelfareIndexDashboard />
      <EconomicDashboard />
    </>
  );
}
