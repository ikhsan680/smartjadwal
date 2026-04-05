import { Clock } from "lucide-react";

interface ScheduleEntry {
  subject: string;
  teacher: string;
  room: string;
  time: string;
}

type WeekSchedule = {
  [key: string]: ScheduleEntry[];
};

export function StudentSchedulePage() {
  const studentClass = sessionStorage.getItem("studentClass") || "X RPL 1";

  const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

  const weekSchedule: WeekSchedule = {
    "Monday": [
      { time: "07:30 - 09:00", subject: "Mathematics", teacher: "John Doe", room: "R101" },
      { time: "09:15 - 10:45", subject: "English", teacher: "Jane Smith", room: "R102" },
      { time: "11:00 - 12:30", subject: "Physics", teacher: "David Lee", room: "R103" },
      { time: "12:45 - 14:15", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
    ],
    "Tuesday": [
      { time: "07:30 - 09:00", subject: "Database", teacher: "Alice Brown", room: "Lab 2" },
      { time: "09:15 - 10:45", subject: "Web Design", teacher: "Charlie Davis", room: "Lab 1" },
      { time: "11:00 - 12:30", subject: "Chemistry", teacher: "Emma White", room: "Lab 3" },
      { time: "14:30 - 16:00", subject: "Physical Education", teacher: "Frank Green", room: "Field" },
    ],
    "Wednesday": [
      { time: "07:30 - 09:00", subject: "Indonesian", teacher: "John Doe", room: "R104" },
      { time: "09:15 - 10:45", subject: "Mathematics", teacher: "John Doe", room: "R101" },
      { time: "11:00 - 12:30", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
      { time: "12:45 - 14:15", subject: "Computer Network", teacher: "Charlie Davis", room: "Lab 2" },
    ],
    "Thursday": [
      { time: "07:30 - 09:00", subject: "English", teacher: "Jane Smith", room: "R102" },
      { time: "09:15 - 10:45", subject: "Database", teacher: "Alice Brown", room: "Lab 2" },
      { time: "11:00 - 12:30", subject: "History", teacher: "David Lee", room: "R105" },
      { time: "12:45 - 14:15", subject: "Web Design", teacher: "Charlie Davis", room: "Lab 1" },
    ],
    "Friday": [
      { time: "07:30 - 09:00", subject: "Project Management", teacher: "Frank Green", room: "R201" },
      { time: "09:15 - 10:45", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
    ],
  };

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Weekly Schedule</h1>
        <p className="text-gray-600">Your class schedule for {studentClass}</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        {days.map((day) => (
          <div key={day} className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div className="bg-blue-600 px-4 py-3">
              <h3 className="text-white text-center">{day}</h3>
            </div>
            <div className="p-4 space-y-3">
              {weekSchedule[day] && weekSchedule[day].length > 0 ? (
                weekSchedule[day].map((schedule, index) => (
                  <div
                    key={index}
                    className="bg-blue-50 border-l-4 border-blue-600 rounded p-3 hover:shadow-md transition-shadow"
                  >
                    <div className="flex items-center gap-2 mb-2 text-xs text-blue-700">
                      <Clock className="w-3 h-3" />
                      <span>{schedule.time}</span>
                    </div>
                    <div className="mb-2">
                      <p className="text-sm text-gray-900">{schedule.subject}</p>
                    </div>
                    <div className="text-xs text-gray-600 space-y-1">
                      <p>{schedule.teacher}</p>
                      <p className="text-gray-500">{schedule.room}</p>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center text-gray-400 py-8 text-sm">
                  No classes
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      <div className="mt-8 bg-blue-50 border border-blue-100 rounded-xl p-6">
        <div className="flex items-start gap-3">
          <div className="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
            <Clock className="w-5 h-5 text-blue-600" />
          </div>
          <div>
            <h3 className="text-lg text-gray-900 mb-2">Class Schedule Notes</h3>
            <ul className="text-sm text-gray-700 space-y-1">
              <li>• Please arrive at class 5 minutes before the scheduled time</li>
              <li>• Bring all necessary materials for each subject</li>
              <li>• Check for any schedule changes or announcements</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
