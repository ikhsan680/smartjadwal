import { School, Calendar, BookOpen, Clock } from "lucide-react";

export function StudentDashboard() {
  const studentName = sessionStorage.getItem("studentName") || "Student";
  const studentClass = sessionStorage.getItem("studentClass") || "X RPL 1";

  // Mock today's schedule
  const todaySchedule = [
    { time: "07:30 - 09:00", subject: "Mathematics", teacher: "John Doe", room: "R101" },
    { time: "09:15 - 10:45", subject: "English", teacher: "Jane Smith", room: "R102" },
    { time: "11:00 - 12:30", subject: "Physics", teacher: "David Lee", room: "R103" },
    { time: "12:45 - 14:15", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
  ];

  const totalSubjects = 12;

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Welcome, {studentName}!</h1>
        <p className="text-gray-600">Here's your schedule overview</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <School className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <p className="text-sm text-gray-600">Your Class</p>
              <h3 className="text-2xl text-gray-900">{studentClass}</h3>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <Calendar className="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p className="text-sm text-gray-600">Today's Classes</p>
              <h3 className="text-2xl text-gray-900">{todaySchedule.length}</h3>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <BookOpen className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p className="text-sm text-gray-600">Total Subjects</p>
              <h3 className="text-2xl text-gray-900">{totalSubjects}</h3>
            </div>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="p-6 border-b border-gray-200">
          <h2 className="text-xl text-gray-900 flex items-center gap-2">
            <Clock className="w-5 h-5 text-blue-600" />
            Today's Schedule - {new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
          </h2>
        </div>
        <div className="p-6">
          <div className="space-y-4">
            {todaySchedule.map((schedule, index) => (
              <div
                key={index}
                className="flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-blue-50 border-l-4 border-blue-600 rounded-lg hover:shadow-md transition-shadow"
              >
                <div className="flex-shrink-0 sm:w-32">
                  <div className="flex items-center gap-2 text-blue-700">
                    <Clock className="w-4 h-4" />
                    <span className="text-sm">{schedule.time}</span>
                  </div>
                </div>
                <div className="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-4">
                  <div>
                    <p className="text-xs text-gray-600 mb-1">Subject</p>
                    <p className="text-sm text-gray-900">{schedule.subject}</p>
                  </div>
                  <div>
                    <p className="text-xs text-gray-600 mb-1">Teacher</p>
                    <p className="text-sm text-gray-900">{schedule.teacher}</p>
                  </div>
                  <div>
                    <p className="text-xs text-gray-600 mb-1">Room</p>
                    <p className="text-sm text-gray-900">{schedule.room}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-sm p-6 text-white">
          <h3 className="text-lg mb-2">Next Class</h3>
          <p className="text-2xl mb-1">Mathematics</p>
          <p className="text-blue-100">Starting at 07:30 AM</p>
        </div>

        <div className="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-sm p-6 text-white">
          <h3 className="text-lg mb-2">Quick Tip</h3>
          <p className="text-sm text-purple-100">
            Check your weekly schedule to stay prepared for upcoming classes!
          </p>
        </div>
      </div>
    </div>
  );
}
