import { Users, School, BookOpen, Calendar } from "lucide-react";

export function Dashboard() {
  const stats = [
    {
      title: "Total Teachers",
      value: "45",
      icon: Users,
      color: "bg-blue-500",
      change: "+3 this month",
    },
    {
      title: "Total Classes",
      value: "24",
      icon: School,
      color: "bg-green-500",
      change: "+2 this semester",
    },
    {
      title: "Total Subjects",
      value: "18",
      icon: BookOpen,
      color: "bg-purple-500",
      change: "No changes",
    },
    {
      title: "Total Schedules",
      value: "156",
      icon: Calendar,
      color: "bg-orange-500",
      change: "+12 this week",
    },
  ];

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Dashboard</h1>
        <p className="text-gray-600">Welcome to Smart Jadwal School Management System</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {stats.map((stat, index) => (
          <div key={index} className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div className="flex items-start justify-between mb-4">
              <div>
                <p className="text-sm text-gray-600 mb-1">{stat.title}</p>
                <h3 className="text-3xl text-gray-900">{stat.value}</h3>
              </div>
              <div className={`${stat.color} p-3 rounded-lg`}>
                <stat.icon className="w-6 h-6 text-white" />
              </div>
            </div>
            <p className="text-xs text-gray-500">{stat.change}</p>
          </div>
        ))}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 className="text-xl text-gray-900 mb-4">Recent Activities</h2>
          <div className="space-y-4">
            {[
              { action: "New schedule created", class: "X RPL 1", time: "2 hours ago" },
              { action: "Teacher added", class: "Mathematics", time: "5 hours ago" },
              { action: "Schedule updated", class: "XI RPL 2", time: "1 day ago" },
              { action: "New subject added", class: "Web Programming", time: "2 days ago" },
            ].map((activity, index) => (
              <div key={index} className="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0">
                <div className="w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                <div className="flex-1">
                  <p className="text-sm text-gray-900">{activity.action}</p>
                  <p className="text-xs text-gray-600">{activity.class}</p>
                </div>
                <span className="text-xs text-gray-500">{activity.time}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 className="text-xl text-gray-900 mb-4">Quick Stats</h2>
          <div className="space-y-4">
            <div>
              <div className="flex justify-between items-center mb-2">
                <span className="text-sm text-gray-600">Schedule Completion</span>
                <span className="text-sm text-gray-900">85%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div className="bg-blue-600 h-2 rounded-full" style={{ width: "85%" }}></div>
              </div>
            </div>
            <div>
              <div className="flex justify-between items-center mb-2">
                <span className="text-sm text-gray-600">Teacher Attendance</span>
                <span className="text-sm text-gray-900">92%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div className="bg-green-600 h-2 rounded-full" style={{ width: "92%" }}></div>
              </div>
            </div>
            <div>
              <div className="flex justify-between items-center mb-2">
                <span className="text-sm text-gray-600">Room Utilization</span>
                <span className="text-sm text-gray-900">78%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div className="bg-purple-600 h-2 rounded-full" style={{ width: "78%" }}></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
