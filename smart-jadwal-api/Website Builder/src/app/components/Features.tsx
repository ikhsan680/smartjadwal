import { Zap, Shield, Sparkles, Users, TrendingUp, Award } from "lucide-react";

export function Features() {
  const features = [
    {
      icon: <Zap className="w-8 h-8 text-blue-600" />,
      title: "Lightning Fast",
      description: "Experience blazing fast performance with our optimized solutions.",
    },
    {
      icon: <Shield className="w-8 h-8 text-blue-600" />,
      title: "Secure & Reliable",
      description: "Your data is protected with industry-leading security measures.",
    },
    {
      icon: <Sparkles className="w-8 h-8 text-blue-600" />,
      title: "Modern Design",
      description: "Beautiful, intuitive interfaces that users love to interact with.",
    },
    {
      icon: <Users className="w-8 h-8 text-blue-600" />,
      title: "Team Collaboration",
      description: "Work together seamlessly with powerful collaboration tools.",
    },
    {
      icon: <TrendingUp className="w-8 h-8 text-blue-600" />,
      title: "Analytics",
      description: "Make data-driven decisions with comprehensive analytics.",
    },
    {
      icon: <Award className="w-8 h-8 text-blue-600" />,
      title: "Award Winning",
      description: "Recognized for excellence and innovation in the industry.",
    },
  ];

  return (
    <section id="features" className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-4xl mb-4 text-gray-900">
            Everything You Need
          </h2>
          <p className="text-xl text-gray-600">
            Powerful features to help you succeed
          </p>
        </div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <div
              key={index}
              className="p-6 border border-gray-200 rounded-lg hover:shadow-lg transition-shadow"
            >
              <div className="mb-4">{feature.icon}</div>
              <h3 className="text-xl mb-2 text-gray-900">{feature.title}</h3>
              <p className="text-gray-600">{feature.description}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
