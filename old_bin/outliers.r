#!/usr/bin/Rscript
args <- commandArgs(TRUE)
X = as.numeric(c(args))
cat(toString(unlist(boxplot.stats(X, coef=1.5)[4])))

