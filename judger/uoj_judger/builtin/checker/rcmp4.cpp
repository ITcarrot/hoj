#include "testlib.h"
#include <string>
#include <sstream>

using namespace std;

const double EPS = 1E-4;

bool isNum(const string &s)
{
	stringstream ss(s);
	double d;
	char c;
	if(!(ss >> d))
		return 0;
	if(ss >> c)
		return 0;
	return 1;
}

double to_double(const string &s)
{
	stringstream ss(s);
	double d;
	ss >> d;
	return d;
}

int main(int argc, char * argv[])
{
    setName("compare two sequences of doubles or strings, max absolute error = %.10lf", EPS);
    registerTestlibCmd(argc, argv);

    int n = 0;
    std::string j,p;
    double dj,dp;
    while (!ans.seekEof()) 
    {
        j = ans.readToken();
        p = ouf.readToken();
        n++;
        
        if(isNum(j)){
        	if(!isNum(p)){
				quitf(_wa, "%d%s element differ - expected: '%s', found: '%s'",
					n, englishEnding(n).c_str(), compress(j).c_str(), compress(p).c_str());
			}
			dj = to_double(j);
			dp = to_double(p);
			if(fabs(dj-dp) > EPS){
				quitf(_wa, "%d%s element differ - expected: '%.10lf', found: '%.10lf', error = '%.10lf'",
					n, englishEnding(n).c_str(), dj, dp, fabs(dj-dp));
			}
		}else if(j!=p){
			quitf(_wa, "%d%s element differ - expected: '%s', found: '%s'",
				n, englishEnding(n).c_str(), compress(j).c_str(), compress(p).c_str());
        }
    }
    
    quitf(_ok, "%d elements", n);
}
